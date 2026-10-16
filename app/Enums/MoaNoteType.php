<?php

namespace App\Enums;

enum MoaNoteType: string
{
    case LEGAL_REMARK = 'legal_remark';
    case REVISION = 'revision';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::LEGAL_REMARK => 'Legal Counsel Remark',
            self::REVISION => 'Required Revision',
            self::GENERAL => 'General Internal Note',
        };
    }
}
