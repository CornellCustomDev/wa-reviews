<?php

namespace App\Enums;

enum Agents: string
{
    use NamedEnum;

    case GuidelinesAnalyzer = 'GuidelinesAnalyzer';
}
