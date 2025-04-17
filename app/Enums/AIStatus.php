<?php

namespace App\Enums;

enum AIStatus: string
{
    use NamedEnum;

    case Generated = 'generated';
    case Modified = 'modified';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
}
