<?php

namespace App\Enums;

enum CompanyClassification: string
{
    case NEW_COMPANY = 'new_company';
    case EXISTING_PARTNER = 'existing_partner';

    public function label(): string
    {
        return match ($this) {
            self::NEW_COMPANY => 'New Company',
            self::EXISTING_PARTNER => 'Existing Partner',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::NEW_COMPANY => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::EXISTING_PARTNER => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
        };
    }
}
