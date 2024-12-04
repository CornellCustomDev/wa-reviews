<?php

namespace App\Enums;

enum GuidelineTools: string
{
    use NamedEnum;

    case Siteimprove = 'Siteimprove';
    case Manual = 'Manual';
    case Keyboard = 'Keyboard';
    case ScreenReader = 'Screen reader';
    case BrowserExtension = 'Browser extension';
    case ColorContrastAnalyzer = 'Color contrast analyzer';
    case Other = 'See notes';
}
