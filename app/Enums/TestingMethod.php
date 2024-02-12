<?php

namespace App\Enums;

enum TestingMethod: string
{
    use NamedEnum;

    case ANDI = 'andi';
    case AXE = 'aXe Extension';
    case CCA = 'Color Contract Analyzer';
    case KEYBOARD = 'Keyboard';
    case MANUAL = 'Manual Test (Other)';
    case SR = 'Screen Reader';
    case SI = 'Site Improve';
    case WAVE = 'WAVE Extension';
    case OTHER = 'Other';
}
