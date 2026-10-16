<?php

namespace App\Enums;

enum MoaRiskLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low Risk',
            self::MEDIUM => 'Medium Risk',
            self::HIGH => 'High Risk',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::LOW => 'bg-success/10 text-success border border-success/20',
            self::MEDIUM => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::HIGH => 'bg-danger/10 text-danger border border-danger/20',
        };
    }
}
