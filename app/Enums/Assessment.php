<?php

namespace App\Enums;

enum Assessment: string
{
    use NamedEnum;

    case Fail = 'Fail';
    case Warn = 'Warn';
    case Pass = 'Pass';
    case Not_Applicable = 'N/A';
}
