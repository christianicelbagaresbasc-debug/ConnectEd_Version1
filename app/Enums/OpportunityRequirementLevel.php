<?php

namespace App\Enums;

enum OpportunityRequirementLevel: string
{
    case REQUIRED = 'required';
    case PREFERRED = 'preferred';

    public function label(): string
    {
        return match ($this) {
            self::REQUIRED => 'Required',
            self::PREFERRED => 'Preferred',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::REQUIRED => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20',
            self::PREFERRED => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
        };
    }

    public function weight(): int
    {
        return match ($this) {
            self::REQUIRED => 2,
            self::PREFERRED => 1,
        };
    }
}
