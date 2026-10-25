<?php

namespace App\Enums;

enum PlacementStatus: string
{
    case PLACED = 'placed';
    case AUTHORIZED = 'authorized';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
    case TERMINATED = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::PLACED => 'Placed (Pending Start Authorization)',
            self::AUTHORIZED => 'Authorized to Start',
            self::ONGOING => 'Ongoing Internship',
            self::COMPLETED => 'Completed',
            self::TERMINATED => 'Terminated',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PLACED => 'bg-info/10 text-info border-info/20',
            self::AUTHORIZED => 'bg-primary/10 text-primary border-primary/20',
            self::ONGOING => 'bg-success/10 text-success border-success/20',
            self::COMPLETED => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            self::TERMINATED => 'bg-danger/10 text-danger border-danger/20',
        };
    }
}
