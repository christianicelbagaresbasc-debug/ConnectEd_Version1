<?php

namespace App\Policies;

use App\Enums\MoaStatus;
use App\Enums\MoaType;
use App\Enums\UserRole;
use App\Models\Moa;
use App\Models\User;

class MoaPolicy
{
    /**
     * Determine whether the user can view any MOAs.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [
            UserRole::OC,
            UserRole::COC,
            UserRole::UA,
            UserRole::PC,
            UserRole::STUDENT,
        ]);
    }

    /**
     * Determine whether the user can access the University Attorney legal review queue.
     */
    public function viewLegalQueue(User $user): bool
    {
        return in_array($user->role, [
            UserRole::OC,
            UserRole::UA,
        ]);
    }

    /**
     * Determine whether the user can view the specific MOA.
     */
    public function view(User $user, Moa $moa): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC, UserRole::UA])) {
            return true;
        }

        if ($user->isPc()) {
            return $user->partnerAccount && (string)$user->partnerAccount->company_id === (string)$moa->company_id;
        }

        if ($user->isStudent()) {
            // Student can view their own student-specific MOA
            if ($user->studentProfile && (string)$user->studentProfile->id === (string)$moa->student_profile_id) {
                return true;
            }

            // Or an active institutional umbrella MOA
            return $moa->type === MoaType::UNIVERSITY_COMPANY && $moa->status === MoaStatus::ACTIVE;
        }

        return false;
    }

    /**
     * Determine whether the user can create an MOA record.
     */
    public function create(User $user): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can update the MOA.
     */
    public function update(User $user, Moa $moa): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::UA]);
    }

    /**
     * Determine whether the user can soft delete the MOA.
     */
    public function delete(User $user, Moa $moa): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can transfer custody (BR-16 unconstrained order).
     */
    public function transferCustody(User $user, Moa $moa): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC, UserRole::UA])) {
            return true;
        }

        if ($user->isPc() && $user->partnerAccount && (string)$user->partnerAccount->company_id === (string)$moa->company_id) {
            return true;
        }

        if ($user->isStudent() && $user->studentProfile && (string)$user->studentProfile->id === (string)$moa->student_profile_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view internal legal notes (Rule BR-18).
     * Strictly restricted to university staff (OC, COC, UA). Never PC or Student.
     */
    public function viewInternalNotes(User $user, Moa $moa): bool
    {
        return in_array($user->role, [
            UserRole::OC,
            UserRole::COC,
            UserRole::UA,
        ]);
    }

    /**
     * Determine whether the user can add an internal legal review note.
     */
    public function addInternalNote(User $user, Moa $moa): bool
    {
        return in_array($user->role, [
            UserRole::OC,
            UserRole::COC,
            UserRole::UA,
        ]);
    }

    /**
     * Determine whether the user can resolve a legal review note.
     */
    public function resolveNote(User $user, Moa $moa): bool
    {
        return in_array($user->role, [
            UserRole::OC,
            UserRole::UA,
        ]);
    }
}
