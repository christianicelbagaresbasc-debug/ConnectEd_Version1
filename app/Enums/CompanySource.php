<?php

namespace App\Enums;

enum CompanySource: string
{
    case OC_CREATED = 'oc_created';
    case STUDENT_SUGGESTION = 'student_suggestion';

    public function label(): string
    {
        return match ($this) {
            self::OC_CREATED => 'OJT Coordinator Created',
            self::STUDENT_SUGGESTION => 'Student Suggestion',
        };
    }
}
