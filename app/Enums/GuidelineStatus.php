<?php

namespace App\Enums;

enum GuidelineStatus: string
{
    use NamedEnum;

    case NotStarted = 'Not Started';
    case NotApplicable = 'Not Applicable';
    case Reviewed = 'Reviewed';
}
