<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::LOW => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            self::MEDIUM => 'bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300 border-primary-200 dark:border-primary-800',
            self::HIGH => 'bg-danger/10 text-danger border-danger/20',
        };
    }
}
