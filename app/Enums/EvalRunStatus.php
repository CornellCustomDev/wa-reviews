<?php

namespace App\Enums;

enum EvalRunStatus: string
{
    use NamedEnum;

    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
