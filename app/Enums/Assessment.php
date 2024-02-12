<?php

namespace App\Enums;

enum Assessment: string
{
    use NamedEnum;

    case Pass = 'Pass';
    case Warn = 'Warn';
    case Fail = 'Fail';
    case Not_Applicable = 'N/A';
}
