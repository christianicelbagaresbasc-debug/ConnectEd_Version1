<?php

namespace App\Enums;

enum CompanyVerificationStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::UNDER_REVIEW => 'Under Review',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Rejected',
            self::REVOKED => 'Revoked',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::UNDER_REVIEW => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
            self::VERIFIED => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::REJECTED => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20',
            self::REVOKED => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
        };
    }
}
