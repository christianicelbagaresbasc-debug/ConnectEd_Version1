<?php

namespace App\Enums;

enum MoaCustodianParty: string
{
    case OC = 'oc';
    case COC = 'coc';
    case UA = 'ua';
    case STUDENT = 'student';
    case COMPANY = 'company';

    public function label(): string
    {
        return match ($this) {
            self::OC => 'OJT Coordinator (OC)',
            self::COC => 'Co-Coordinator (COC)',
            self::UA => 'University Attorney (UA)',
            self::STUDENT => 'Student Intern',
            self::COMPANY => 'Partner Company (PC)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::OC => 'OC',
            self::COC => 'COC',
            self::UA => 'Legal (UA)',
            self::STUDENT => 'Student',
            self::COMPANY => 'Company',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::OC => 'bg-primary/10 text-primary border border-primary/20',
            self::COC => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20',
            self::UA => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20',
            self::STUDENT => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::COMPANY => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
        };
    }
}
