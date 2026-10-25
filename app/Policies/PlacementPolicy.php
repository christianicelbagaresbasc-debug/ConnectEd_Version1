<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Placement;
use App\Models\User;

class PlacementPolicy
{
    /**
     * Determine whether the user can view any placements.
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
     * Determine whether the user can view the placement.
     */
    public function view(User $user, Placement $placement): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC])) {
            return true;
        }

        if ($user->role === UserRole::STUDENT) {
            return $placement->studentProfile && $placement->studentProfile->user_id === $user->id;
        }

        if ($user->role === UserRole::PC) {
            return $user->partnerAccount && $user->partnerAccount->company_id === $placement->company_id;
        }

        return false;
    }

    /**
     * Determine whether coordinator can authorize start of internship.
     */
    public function authorize(User $user, Placement $placement): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC])
            && $placement->is_active;
    }

    /**
     * Determine whether coordinator can update placement status.
     */
    public function updateStatus(User $user, Placement $placement): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC]);
    }
}
