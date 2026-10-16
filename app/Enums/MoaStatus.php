<?php

namespace App\Enums;

enum MoaStatus: string
{
    case DRAFT = 'draft';
    case UNDER_LEGAL_REVIEW = 'under_legal_review';
    case FOR_SIGNING_SCHOOL = 'for_signing_school';
    case FOR_SIGNING_COMPANY = 'for_signing_company';
    case NOTARIZED = 'notarized';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case TERMINATED = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::UNDER_LEGAL_REVIEW => 'Under Legal Review',
            self::FOR_SIGNING_SCHOOL => 'For University Signing',
            self::FOR_SIGNING_COMPANY => 'For Company Signing',
            self::NOTARIZED => 'Notarized',
            self::ACTIVE => 'Active / Executed',
            self::EXPIRED => 'Expired',
            self::TERMINATED => 'Terminated',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-success/10 text-success border border-success/20',
            self::UNDER_LEGAL_REVIEW => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20',
            self::FOR_SIGNING_SCHOOL, self::FOR_SIGNING_COMPANY => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20',
            self::NOTARIZED => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
            self::DRAFT => 'bg-surface-2 text-text-muted border border-border',
            self::EXPIRED => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::TERMINATED => 'bg-danger/10 text-danger border border-danger/20',
        };
    }
}
