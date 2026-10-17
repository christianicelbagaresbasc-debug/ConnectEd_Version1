<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case DRAFT = 'draft';
    case PENDING_APPROVAL = 'pending_approval';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING_APPROVAL => 'Pending Approval',
            self::OPEN => 'Open',
            self::CLOSED => 'Closed',
            self::ARCHIVED => 'Archived',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border border-slate-500/20',
            self::PENDING_APPROVAL => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
            self::OPEN => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
            self::CLOSED => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20',
            self::ARCHIVED => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20',
        };
    }

    public function isAcceptingApplications(): bool
    {
        return $this === self::OPEN;
    }
}
