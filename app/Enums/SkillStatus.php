<?php

namespace App\Enums;

enum SkillStatus: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::PENDING => 'Pending Review',
            self::INACTIVE => 'Inactive',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::PENDING => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::INACTIVE => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20',
        };
    }
}
