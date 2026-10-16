<?php

namespace App\Enums;

enum MoaType: string
{
    case UNIVERSITY_COMPANY = 'university_company';
    case STUDENT_COMPANY = 'student_company';

    public function label(): string
    {
        return match ($this) {
            self::UNIVERSITY_COMPANY => 'University-Wide (Umbrella)',
            self::STUDENT_COMPANY => 'Student-Specific',
        };
    }
}
