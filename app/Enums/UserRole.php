<?php

namespace App\Enums;

enum UserRole: string
{
    case OC = 'oc';
    case COC = 'coc';
    case UA = 'ua';
    case PC = 'pc';
    case STUDENT = 'student';

    public function label(): string
    {
        return match ($this) {
            self::OC => 'OJT Coordinator',
            self::COC => 'Co-OJT Coordinator',
            self::UA => 'University Attorney',
            self::PC => 'Partner Company Representative',
            self::STUDENT => 'ICS Student',
        };
    }
}
