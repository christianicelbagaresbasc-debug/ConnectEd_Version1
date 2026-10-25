<?php

namespace App\Policies;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\OpportunityStatus;
use App\Enums\UserRole;
use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
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

    public function view(User $user, Opportunity $opportunity): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC, UserRole::UA])) {
            return true;
        }

        if ($user->isPc()) {
            return $user->partnerAccount && (string) $user->partnerAccount->company_id === (string) $opportunity->company_id;
        }

        if ($user->isStudent()) {
            // Students can only view open opportunities from active verified existing partners
            $company = $opportunity->company;
            if (! $company || ! $company->is_active || $company->classification !== CompanyClassification::EXISTING_PARTNER || $company->verification_status !== CompanyVerificationStatus::VERIFIED) {
                return false;
            }

            return $opportunity->status === OpportunityStatus::OPEN;
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isOc()) {
            return true;
        }

        if ($user->isPc() && $user->partnerAccount) {
            $company = $user->partnerAccount->company;

            return $company && $company->is_active;
        }

        return false;
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        if ($user->isOc()) {
            return true;
        }

        if ($user->isPc() && $user->partnerAccount && (string) $user->partnerAccount->company_id === (string) $opportunity->company_id) {
            return in_array($opportunity->status, [OpportunityStatus::DRAFT, OpportunityStatus::PENDING_APPROVAL]);
        }

        return false;
    }

    public function approve(User $user, Opportunity $opportunity): bool
    {
        return $user->isOc();
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        if ($user->isOc()) {
            return true;
        }

        if ($user->isPc() && $user->partnerAccount && (string) $user->partnerAccount->company_id === (string) $opportunity->company_id) {
            return $opportunity->status === OpportunityStatus::DRAFT;
        }

        return false;
    }
}
