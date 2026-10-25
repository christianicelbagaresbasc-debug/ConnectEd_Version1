<?php

namespace App\Enums;

enum WeeklyReportStatus: string
{
    case SUBMITTED = 'submitted';
    case REVIEWED = 'reviewed';
    case FLAGGED = 'flagged';

    public function label(): string
    {
        return match ($this) {
            self::SUBMITTED => 'Submitted (Pending Review)',
            self::REVIEWED => 'Reviewed & Approved',
            self::FLAGGED => 'Flagged for Revision',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::SUBMITTED => 'bg-warning/10 text-warning border-warning/20',
            self::REVIEWED => 'bg-success/10 text-success border-success/20',
            self::FLAGGED => 'bg-danger/10 text-danger border-danger/20',
        };
    }
}
