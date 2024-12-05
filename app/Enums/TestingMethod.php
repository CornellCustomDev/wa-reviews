<?php

namespace App\Enums;

enum TestingMethod: string
{
    use NamedEnum;

    case ANDI = 'ANDI';
    case AXE = 'aXe Extension';
    case CCA = 'Color Contrast Analyzer';
    case KEYBOARD = 'Keyboard';
    case MANUAL = 'Manual Test (Other)';
    case SR = 'Screen Reader';
    case SI = 'Siteimprove';
    case WAVE = 'WAVE Extension';
}
