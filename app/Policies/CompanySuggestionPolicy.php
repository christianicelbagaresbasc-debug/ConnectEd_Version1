<?php

namespace App\Policies;

use App\Models\CompanySuggestion;
use App\Models\User;

class CompanySuggestionPolicy
{
    /**
     * Determine whether the user can view suggestions queue or list.
     */
    public function viewAny(User $user): bool
    {
        return $user->isOc() || $user->isStudent();
    }

    /**
     * Determine whether the user can create a company suggestion.
     */
    public function create(User $user): bool
    {
        return $user->isStudent() && $user->studentProfile !== null;
    }

    /**
     * Determine whether the user can handle (convert/dismiss) a suggestion.
     */
    public function handle(User $user, CompanySuggestion $suggestion): bool
    {
        return $user->isOc();
    }
}
