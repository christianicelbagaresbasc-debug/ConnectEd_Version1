<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC]);
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        if ($user->role === UserRole::OC) {
            return true;
        }

        if ($user->role === UserRole::COC) {
            return $task->assigned_to === $user->id || $task->created_by === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::OC;
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        if ($user->role === UserRole::OC) {
            return true;
        }

        if ($user->role === UserRole::COC) {
            return $task->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->role === UserRole::OC;
    }
}
