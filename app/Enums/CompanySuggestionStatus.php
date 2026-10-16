<?php

namespace App\Enums;

enum CompanySuggestionStatus: string
{
    case SUBMITTED = 'submitted';
    case CONVERTED = 'converted';
    case DISMISSED = 'dismissed';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted',
            self::CONVERTED => 'Converted to Company',
            self::DISMISSED => 'Dismissed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::SUBMITTED => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::CONVERTED => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::DISMISSED => 'bg-gray-500/10 text-gray-600 dark:text-gray-400 border border-gray-500/20',
        };
    }
}
