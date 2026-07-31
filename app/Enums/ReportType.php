<?php

namespace App\Enums;

enum ReportType: string
{
    use NamedEnum;

    case Review       = 'review';
    case Verification = 'verification';
}
