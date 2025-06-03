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
    public function getDescription(): string
    {
        return match ($this) {
            self::Fail => 'Failure',
            self::Warn => 'Warning',
            self::Pass => 'Passing',
            self::Not_Applicable => 'N/A',
        };
    }

    public function getLongDescription(): string
    {
        return match ($this) {
            self::Fail => 'The item does not meet the success criterion.',
            self::Warn => "No strict failures, but the user's experience is negatively impacted or goes against best practices.",
            self::Pass => 'The item meets the success criterion.',
            self::Not_Applicable => 'The item is not applicable to the success criterion.',
        };
    }
}
