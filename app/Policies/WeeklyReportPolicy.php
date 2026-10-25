<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Placement;
use App\Models\User;
use App\Models\WeeklyReport;

class WeeklyReportPolicy
{
    /**
     * Determine whether the user can upload a weekly report for a placement.
     */
    public function create(User $user, Placement $placement): bool
    {
        return $user->role === UserRole::STUDENT
            && $placement->studentProfile
            && $placement->studentProfile->user_id === $user->id
            && $placement->is_active;
    }

    /**
     * Determine whether the user can view the weekly report.
     */
    public function view(User $user, WeeklyReport $report): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC])) {
            return true;
        }

        if ($user->role === UserRole::STUDENT) {
            return $report->placement && $report->placement->studentProfile && $report->placement->studentProfile->user_id === $user->id;
        }

        if ($user->role === UserRole::PC) {
            return $user->partnerAccount && $report->placement && $user->partnerAccount->company_id === $report->placement->company_id;
        }

        return false;
    }

    /**
     * Determine whether the coordinator can review the weekly report.
     */
    public function review(User $user, WeeklyReport $report): bool
    {
        return in_array($user->role, [UserRole::OC, UserRole::COC]);
    }
}
