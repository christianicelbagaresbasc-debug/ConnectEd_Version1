<?php

namespace App\Enums;

enum OpportunityWorkArrangement: string
{
    case ONSITE = 'onsite';
    case REMOTE = 'remote';
    case HYBRID = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::ONSITE => 'On-Site',
            self::REMOTE => 'Remote',
            self::HYBRID => 'Hybrid',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ONSITE => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
            self::REMOTE => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::HYBRID => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
        };
    }
}
