<?php

namespace App\Enums;

enum VerificationItemStatus: string
{
    case MISSING = 'missing';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::MISSING => 'Missing',
            self::SUBMITTED => 'Submitted',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::MISSING => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20',
            self::SUBMITTED => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
            self::ACCEPTED => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::REJECTED => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20',
        };
    }
}
