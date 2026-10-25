<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\OpportunityStatus;
use App\Enums\PlacementStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationStatusLog;
use App\Models\Opportunity;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\User;
use App\Notifications\ConnectEdNotification;
use DomainException;
use Illuminate\Support\Facades\DB;

class PlacementService
{
    /**
     * Submit a new application for a student.
     */
    public function submitApplication(StudentProfile $studentProfile, Opportunity $opportunity, ?string $coverNote = null): Application
    {
        // Student cannot apply if they have an active placement
        if ($studentProfile->has_active_placement) {
            throw new DomainException('You already have an active placement and cannot apply for additional opportunities.');
        }

        // Student cannot apply if opportunity is not open
        if (! $opportunity->isOpen()) {
            throw new DomainException('This opportunity is no longer open for applications.');
        }

        // Student cannot apply more than once to the same opportunity
        $existing = Application::where('student_profile_id', $studentProfile->id)
            ->where('opportunity_id', $opportunity->id)
            ->first();

        if ($existing) {
            throw new DomainException('You have already applied for this opportunity.');
        }

        return DB::transaction(function () use ($studentProfile, $opportunity, $coverNote) {
            $application = Application::create([
                'student_profile_id' => $studentProfile->id,
                'opportunity_id' => $opportunity->id,
                'status' => ApplicationStatus::PENDING,
                'cover_note' => $coverNote,
                'submitted_at' => now(),
                'school_year' => $opportunity->school_year ?? '2026-2027',
            ]);

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => null,
                'to_status' => ApplicationStatus::PENDING,
                'actor_id' => $studentProfile->user_id,
                'remark' => 'Application submitted by student.',
            ]);

            // Notify coordinators
            $coordinators = User::whereIn('role', [UserRole::OC, UserRole::COC])->get();
            foreach ($coordinators as $coord) {
                $coord->notify(new ConnectEdNotification(
                    title: 'New Application Submitted',
                    body: "Student {$studentProfile->user->name} applied for {$opportunity->title} at {$opportunity->company->name}.",
                    actionUrl: route('coordinator.applications.show', $application),
                    type: 'application_submitted'
                ));
            }

            return $application;
        });
    }

    /**
     * Coordinator/Company marks application under review.
     */
    public function markUnderReview(Application $application, User $actor, ?string $remark = null): Application
    {
        if ($application->status !== ApplicationStatus::PENDING) {
            throw new DomainException('Only pending applications can be marked under review.');
        }

        return DB::transaction(function () use ($application, $actor, $remark) {
            $oldStatus = $application->status;
            $application->update([
                'status' => ApplicationStatus::UNDER_REVIEW,
            ]);

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => ApplicationStatus::UNDER_REVIEW,
                'actor_id' => $actor->id,
                'remark' => $remark ?? 'Application marked under review.',
            ]);

            $application->studentProfile->user->notify(new ConnectEdNotification(
                title: 'Application Under Review',
                body: "Your application for {$application->opportunity->title} at {$application->opportunity->company->name} is now under review.",
                actionUrl: route('student.applications.show', $application),
                type: 'application_under_review'
            ));

            return $application->fresh();
        });
    }

    /**
     * Coordinator/Partner makes an offer (accepts) or rejects an application.
     */
    public function decideApplication(Application $application, ApplicationStatus $decision, ?string $remark, User $actor, bool $autoConfirmOc = false): Application
    {
        if (! in_array($decision, [ApplicationStatus::ACCEPTED, ApplicationStatus::REJECTED])) {
            throw new DomainException('Decision must be accepted or rejected.');
        }

        if (! in_array($application->status, [ApplicationStatus::PENDING, ApplicationStatus::UNDER_REVIEW])) {
            throw new DomainException('Only pending or under-review applications can be decided.');
        }

        return DB::transaction(function () use ($application, $decision, $remark, $actor, $autoConfirmOc) {
            $oldStatus = $application->status;
            $application->status = $decision;
            $application->decided_by = $actor->id;
            $application->decided_at = now();
            $application->decision_remark = $remark;

            if ($decision === ApplicationStatus::ACCEPTED && $autoConfirmOc) {
                $application->oc_confirmed_at = now();
            }

            $application->save();

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => $decision,
                'actor_id' => $actor->id,
                'remark' => $remark ?? ($decision === ApplicationStatus::ACCEPTED ? 'Offer extended to student.' : 'Application rejected.'),
            ]);

            $statusText = $decision === ApplicationStatus::ACCEPTED ? 'accepted' : 'rejected';
            $application->studentProfile->user->notify(new ConnectEdNotification(
                title: 'Application '.ucfirst($statusText),
                body: "Your application for {$application->opportunity->title} at {$application->opportunity->company->name} has been {$statusText}.",
                actionUrl: route('student.applications.show', $application),
                type: 'application_decision'
            ));

            return $application->fresh();
        });
    }

    /**
     * Student confirms acceptance of an offer.
     */
    public function confirmByStudent(Application $application, User $studentUser): Application
    {
        if ($application->studentProfile->user_id !== $studentUser->id) {
            throw new DomainException('Unauthorized. You can only confirm your own application offer.');
        }

        if ($application->status !== ApplicationStatus::ACCEPTED) {
            throw new DomainException('Application must be in accepted status to confirm.');
        }

        if ($application->student_confirmed_at !== null) {
            throw new DomainException('You have already confirmed this offer.');
        }

        return DB::transaction(function () use ($application, $studentUser) {
            $application->student_confirmed_at = now();
            $application->save();

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => ApplicationStatus::ACCEPTED,
                'to_status' => ApplicationStatus::ACCEPTED,
                'actor_id' => $studentUser->id,
                'remark' => 'Student confirmed acceptance of offer.',
            ]);

            // Notify coordinators
            $coordinators = User::whereIn('role', [UserRole::OC, UserRole::COC])->get();
            foreach ($coordinators as $coord) {
                $coord->notify(new ConnectEdNotification(
                    title: 'Student Confirmed Offer',
                    body: "Student {$studentUser->name} accepted the offer for {$application->opportunity->title}.",
                    actionUrl: route('coordinator.applications.show', $application),
                    type: 'offer_confirmed_by_student'
                ));
            }

            // Check if dual confirmation is complete
            if ($application->oc_confirmed_at !== null) {
                $this->finalizePlacement($application, $studentUser);
            }

            return $application->fresh();
        });
    }

    /**
     * Coordinator confirms placement endorsement.
     */
    public function confirmByCoordinator(Application $application, User $coordinator): Application
    {
        if (! in_array($coordinator->role, [UserRole::OC, UserRole::COC])) {
            throw new DomainException('Only OJT coordinators can confirm placement endorsements.');
        }

        if ($application->status !== ApplicationStatus::ACCEPTED) {
            throw new DomainException('Application must be in accepted status to confirm.');
        }

        if ($application->oc_confirmed_at !== null) {
            throw new DomainException('Coordinator confirmation has already been recorded.');
        }

        return DB::transaction(function () use ($application, $coordinator) {
            $application->oc_confirmed_at = now();
            $application->save();

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => ApplicationStatus::ACCEPTED,
                'to_status' => ApplicationStatus::ACCEPTED,
                'actor_id' => $coordinator->id,
                'remark' => 'Coordinator confirmed placement readiness.',
            ]);

            // Check if dual confirmation is complete
            if ($application->student_confirmed_at !== null) {
                $this->finalizePlacement($application, $coordinator);
            } else {
                $application->studentProfile->user->notify(new ConnectEdNotification(
                    title: 'Placement Endorsed by Coordinator',
                    body: "Coordinator {$coordinator->name} confirmed your placement for {$application->opportunity->title}. Please confirm to finalize.",
                    actionUrl: route('student.applications.show', $application),
                    type: 'offer_endorsed_by_coordinator'
                ));
            }

            return $application->fresh();
        });
    }

    /**
     * Student declines an offered slot.
     */
    public function declineByStudent(Application $application, User $studentUser, ?string $reason = null): Application
    {
        if ($application->studentProfile->user_id !== $studentUser->id) {
            throw new DomainException('Unauthorized.');
        }

        if ($application->status !== ApplicationStatus::ACCEPTED) {
            throw new DomainException('Only accepted applications can be declined.');
        }

        return DB::transaction(function () use ($application, $studentUser, $reason) {
            $oldStatus = $application->status;
            $application->status = ApplicationStatus::DECLINED;
            $application->withdrawn_reason = $reason;
            $application->save();

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => ApplicationStatus::DECLINED,
                'actor_id' => $studentUser->id,
                'remark' => $reason ? "Declined by student: {$reason}" : 'Declined by student.',
            ]);

            // Notify coordinators
            $coordinators = User::whereIn('role', [UserRole::OC, UserRole::COC])->get();
            foreach ($coordinators as $coord) {
                $coord->notify(new ConnectEdNotification(
                    title: 'Student Declined Offer',
                    body: "Student {$studentUser->name} declined the offer for {$application->opportunity->title}.".($reason ? " Reason: {$reason}" : ''),
                    actionUrl: route('coordinator.applications.show', $application),
                    type: 'offer_declined_by_student'
                ));
            }

            return $application->fresh();
        });
    }

    /**
     * Student withdraws their application.
     */
    public function withdrawByStudent(Application $application, User $studentUser, ?string $reason = null): Application
    {
        if ($application->studentProfile->user_id !== $studentUser->id) {
            throw new DomainException('Unauthorized.');
        }

        if (! in_array($application->status, [ApplicationStatus::PENDING, ApplicationStatus::UNDER_REVIEW, ApplicationStatus::ACCEPTED])) {
            throw new DomainException('Cannot withdraw application at its current stage.');
        }

        return DB::transaction(function () use ($application, $studentUser, $reason) {
            $oldStatus = $application->status;
            $application->status = ApplicationStatus::WITHDRAWN;
            $application->withdrawn_reason = $reason;
            $application->save();

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => $oldStatus,
                'to_status' => ApplicationStatus::WITHDRAWN,
                'actor_id' => $studentUser->id,
                'remark' => $reason ? "Withdrawn by student: {$reason}" : 'Withdrawn by student.',
            ]);

            return $application->fresh();
        });
    }

    /**
     * Finalize placement once dual confirmation is achieved.
     *
     * Rule BR-19: Enforces concurrency-safe single active placement per student
     * using pessimistic locking (lockForUpdate) and PostgreSQL partial unique index.
     * Assumption A6: Auto-withdraws all other open applications with audit log.
     */
    public function finalizePlacement(Application $application, User $actor): Placement
    {
        return DB::transaction(function () use ($application, $actor) {
            // Pessimistic lock on the student profile to prevent concurrent race conditions (Rule BR-19)
            $studentProfile = StudentProfile::where('id', $application->student_profile_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Strict check: Student must not already have an active placement
            $existingActivePlacement = Placement::where('student_profile_id', $studentProfile->id)
                ->where('is_active', true)
                ->first();

            if ($existingActivePlacement) {
                throw new DomainException('Student already has an active placement. Rule BR-19 prohibits multiple active placements.');
            }

            // Create the official placement record
            $placement = Placement::create([
                'student_profile_id' => $studentProfile->id,
                'company_id' => $application->opportunity->company_id,
                'opportunity_id' => $application->opportunity_id,
                'application_id' => $application->id,
                'status' => PlacementStatus::PLACED,
                'is_active' => true,
                'confirmed_by' => $actor->id,
                'school_year' => $application->school_year ?? '2026-2027',
            ]);

            // Update application status to PLACED
            $oldAppStatus = $application->status;
            $application->status = ApplicationStatus::PLACED;
            $application->save();

            ApplicationStatusLog::create([
                'application_id' => $application->id,
                'from_status' => $oldAppStatus,
                'to_status' => ApplicationStatus::PLACED,
                'actor_id' => $actor->id,
                'remark' => 'Dual confirmation complete. Placement finalized.',
            ]);

            // Mark student profile active placement flag
            $studentProfile->has_active_placement = true;
            $studentProfile->save();

            // Lock and update opportunity slot count
            $opportunity = Opportunity::where('id', $application->opportunity_id)
                ->lockForUpdate()
                ->first();

            if ($opportunity) {
                $opportunity->increment('slots_filled');
                if ($opportunity->slots_filled >= $opportunity->slots_total) {
                    $opportunity->status = OpportunityStatus::CLOSED;
                    $opportunity->save();
                }
            }

            // Assumption A6: Auto-withdraw all remaining open applications of this student
            $otherApplications = Application::where('student_profile_id', $studentProfile->id)
                ->where('id', '!=', $application->id)
                ->whereIn('status', [
                    ApplicationStatus::PENDING,
                    ApplicationStatus::UNDER_REVIEW,
                    ApplicationStatus::ACCEPTED,
                ])
                ->get();

            $companyName = $application->opportunity->company->name ?? 'Partner Company';
            foreach ($otherApplications as $otherApp) {
                $prev = $otherApp->status;
                $otherApp->status = ApplicationStatus::WITHDRAWN;
                $otherApp->withdrawn_reason = "Placement finalized: {$companyName}";
                $otherApp->save();

                ApplicationStatusLog::create([
                    'application_id' => $otherApp->id,
                    'from_status' => $prev,
                    'to_status' => ApplicationStatus::WITHDRAWN,
                    'actor_id' => $actor->id,
                    'remark' => "Auto-withdrawn: Placement finalized at {$companyName} (Assumption A6)",
                ]);
            }

            // In-system notification to student
            $studentProfile->user->notify(new ConnectEdNotification(
                title: 'Official Placement Finalized!',
                body: "Congratulations! Your placement at {$companyName} for {$application->opportunity->title} is officially finalized.",
                actionUrl: route('student.placements.show', $placement),
                type: 'placement_finalized'
            ));

            return $placement;
        });
    }

    /**
     * Authorize student to start internship.
     */
    public function authorizeStart(Placement $placement, User $coordinator, array $data): Placement
    {
        return DB::transaction(function () use ($placement, $data) {
            $placement->update([
                'status' => PlacementStatus::AUTHORIZED,
                'start_authorized_at' => now(),
                'start_authorization_note' => $data['note'] ?? null,
                'start_authorization_document_id' => $data['document_id'] ?? null,
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? null,
            ]);

            $placement->studentProfile->user->notify(new ConnectEdNotification(
                title: 'Internship Start Authorized',
                body: "Coordinator has authorized your internship start date for {$placement->company->name}.",
                actionUrl: route('student.placements.show', $placement),
                type: 'internship_authorized'
            ));

            return $placement->fresh();
        });
    }

    /**
     * Update placement status (ongoing, completed, terminated).
     */
    public function updatePlacementStatus(Placement $placement, PlacementStatus $newStatus, ?string $reason, User $actor): Placement
    {
        return DB::transaction(function () use ($placement, $newStatus, $reason) {
            $updates = ['status' => $newStatus];

            if (in_array($newStatus, [PlacementStatus::COMPLETED, PlacementStatus::TERMINATED])) {
                $updates['is_active'] = false;
                if ($newStatus === PlacementStatus::TERMINATED) {
                    $updates['termination_reason'] = $reason;
                }

                // Update student profile flag
                $profile = $placement->studentProfile;
                $profile->has_active_placement = false;
                $profile->save();
            }

            $placement->update($updates);

            $placement->studentProfile->user->notify(new ConnectEdNotification(
                title: "Placement Status Updated: {$newStatus->label()}",
                body: "Your placement at {$placement->company->name} status is now {$newStatus->label()}.".($reason ? " Note: {$reason}" : ''),
                actionUrl: route('student.placements.show', $placement),
                type: 'placement_status_update'
            ));

            return $placement->fresh();
        });
    }
}
