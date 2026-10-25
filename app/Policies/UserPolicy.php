<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any user models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOc() || $user->isCoc();
    }

    /**
     * Determine whether the user can view the user model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->isOc() || $user->id === $model->id) {
            return true;
        }

        if ($user->isCoc() && $model->isStudent()) {
            return $user->assignedStudents()->where('user_id', $model->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can approve student accounts.
     */
    public function approve(User $user): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can reset passwords for other accounts.
     */
    public function resetPassword(User $user): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can provision staff or partner accounts.
     */
    public function provisionStaff(User $user): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can update the given user model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->isOc() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can archive the given user model.
     */
    public function delete(User $user, User $model): bool
    {
        // Prohibit deleting own account via admin route; OC can archive other users
        return $user->isOc() && $user->id !== $model->id;
    }
}
