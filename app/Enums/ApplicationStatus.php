<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case PENDING = 'pending';
    case UNDER_REVIEW = 'under_review';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case DECLINED = 'declined';
    case PLACED = 'placed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Review',
            self::UNDER_REVIEW => 'Under Review',
            self::ACCEPTED => 'Accepted (Awaiting Confirmation)',
            self::REJECTED => 'Rejected',
            self::WITHDRAWN => 'Withdrawn',
            self::DECLINED => 'Declined by Student',
            self::PLACED => 'Placed',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-warning/10 text-warning border-warning/20',
            self::UNDER_REVIEW => 'bg-info/10 text-info border-info/20',
            self::ACCEPTED => 'bg-primary/10 text-primary border-primary/20',
            self::REJECTED => 'bg-danger/10 text-danger border-danger/20',
            self::WITHDRAWN => 'bg-surface-2 text-text-muted border-border',
            self::DECLINED => 'bg-surface-2 text-danger border-danger/20',
            self::PLACED => 'bg-success/10 text-success border-success/20',
        };
    }
}
