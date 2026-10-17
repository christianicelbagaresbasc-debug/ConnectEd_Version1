<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\StudentProfile;
use App\Models\User;

class StudentProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC]);
    }

    public function view(User $user, StudentProfile $studentProfile): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC])) {
            return true;
        }

        return $user->isStudent() && (string) $user->id === (string) $studentProfile->user_id;
    }

    public function update(User $user, StudentProfile $studentProfile): bool
    {
        if ($user->isOc()) {
            return true;
        }

        return $user->isStudent() && (string) $user->id === (string) $studentProfile->user_id;
    }
}
