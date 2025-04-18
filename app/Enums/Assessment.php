<?php

namespace App\Enums;

enum Assessment: string
{
    use NamedEnum;

    case Fail = 'Fail';
    case Warn = 'Warn';
    case Pass = 'Pass';
    case Not_Applicable = 'N/A';

    // Get descriptions for these
    public function description(): string
    {
        return match ($this) {
            self::Fail => 'Failure',
            self::Warn => 'Warning',
            self::Pass => 'Passing',
            self::Not_Applicable => 'Not applicable',
        };
    }
}
