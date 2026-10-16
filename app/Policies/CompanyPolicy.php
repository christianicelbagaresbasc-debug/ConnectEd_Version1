<?php

namespace App\Policies;

use App\Enums\CompanyClassification;
use App\Enums\CompanyVerificationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any companies.
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
     * Determine whether the user can view the specific company.
     */
    public function view(User $user, Company $company): bool
    {
        if (in_array($user->role, [UserRole::OC, UserRole::COC, UserRole::UA])) {
            return true;
        }

        if ($user->isPc()) {
            // PC can view their own company regardless of status, or any verified company
            if ($user->partnerAccount && (string)$user->partnerAccount->company_id === (string)$company->id) {
                return true;
            }
            return $company->verification_status === CompanyVerificationStatus::VERIFIED;
        }

        if ($user->isStudent()) {
            // Students only see verified companies or existing partners (BR-20, product-spec)
            return $company->verification_status === CompanyVerificationStatus::VERIFIED ||
                   $company->classification === CompanyClassification::EXISTING_PARTNER;
        }

        return false;
    }

    /**
     * Determine whether the user can create new company records.
     * Companies never self-register; created by OC (BR-01).
     */
    public function create(User $user): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        if ($user->isOc()) {
            return true;
        }

        if ($user->isPc()) {
            // PC can update non-regulatory profile information for their own company
            return $user->partnerAccount && (string)$user->partnerAccount->company_id === (string)$company->id;
        }

        return false;
    }

    /**
     * Determine whether the user can soft delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can update verification status / checklist items.
     * Institutional rule A3: COC cannot decide verification or accept/reject.
     */
    public function verify(User $user, Company $company): bool
    {
        return $user->isOc();
    }

    /**
     * Determine whether the user can promote a New Company to Existing Partner.
     * Hard rule BR-17: Only OC decision; no auto-promotion.
     */
    public function promote(User $user, Company $company): bool
    {
        return $user->isOc();
    }
}
