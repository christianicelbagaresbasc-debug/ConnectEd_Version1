<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::TODO => 'bg-slate-100 text-slate-700 dark:bg-slate-850 dark:text-slate-300 border-slate-200 dark:border-slate-700',
            self::IN_PROGRESS => 'bg-warning/10 text-warning border-warning/20',
            self::DONE => 'bg-success/10 text-success border-success/20',
        };
    }
}
