<?php

namespace App\Enums;

enum SkillProficiency: string
{
    case BEGINNER = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED = 'advanced';

    public function label(): string
    {
        return match ($this) {
            self::BEGINNER => 'Beginner',
            self::INTERMEDIATE => 'Intermediate',
            self::ADVANCED => 'Advanced',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::BEGINNER => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20',
            self::INTERMEDIATE => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20',
            self::ADVANCED => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
        };
    }
}
