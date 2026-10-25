<?php

namespace App\Policies;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine whether the user can view any applications.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::OC,
            UserRole::COC,
            UserRole::PC,
            UserRole::STUDENT,
        ]);
    }

    /**
     * Determine whether the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC])) {
            return true;
        }

        if ($user->role === UserRole::STUDENT) {
            return $application->studentProfile && $application->studentProfile->user_id === $user->id;
        }

        if ($user->role === UserRole::PC) {
            return $user->partnerAccount && $application->opportunity && $user->partnerAccount->company_id === $application->opportunity->company_id;
        }

        return false;
    }

    /**
     * Determine whether the user can apply for an opportunity.
     */
    public function create(User $user): bool
    {
        if ($user->role !== UserRole::STUDENT || ! $user->isApproved()) {
            return false;
        }

        $profile = $user->studentProfile;
        if (! $profile || $profile->has_active_placement) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether coordinator can review or decide the application.
     */
    public function decide(User $user, Application $application): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC]);
    }

    /**
     * Determine whether student can confirm the offer.
     */
    public function studentConfirm(User $user, Application $application): bool
    {
        return $user->role === UserRole::STUDENT
            && $application->studentProfile
            && $application->studentProfile->user_id === $user->id
            && $application->status === ApplicationStatus::ACCEPTED
            && $application->student_confirmed_at === null;
    }

    /**
     * Determine whether coordinator can confirm the placement offer.
     */
    public function coordinatorConfirm(User $user, Application $application): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC])
            && $application->status === ApplicationStatus::ACCEPTED
            && $application->oc_confirmed_at === null;
    }

    /**
     * Determine whether student can decline an offered slot.
     */
    public function studentDecline(User $user, Application $application): bool
    {
        return $user->role === UserRole::STUDENT
            && $application->studentProfile
            && $application->studentProfile->user_id === $user->id
            && $application->status === ApplicationStatus::ACCEPTED;
    }

    /**
     * Determine whether student can withdraw the application.
     */
    public function withdraw(User $user, Application $application): bool
    {
        return $user->role === UserRole::STUDENT
            && $application->studentProfile
            && $application->studentProfile->user_id === $user->id
            && in_array($application->status, [
                ApplicationStatus::PENDING,
                ApplicationStatus::UNDER_REVIEW,
                ApplicationStatus::ACCEPTED,
            ]);
    }
}
