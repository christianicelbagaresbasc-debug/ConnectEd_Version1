<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Skill $skill): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isOc();
    }

    public function update(User $user, Skill $skill): bool
    {
        return $user->isOc();
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $user->isOc();
    }

    public function suggest(User $user): bool
    {
        return $user->isStudent() || $user->isOc();
    }
}
