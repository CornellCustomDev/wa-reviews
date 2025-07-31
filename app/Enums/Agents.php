<?php

namespace App\Enums;

enum Agents: string
{
    use NamedEnum;

    case GuidelinesAnalyzer = 'GuidelinesAnalyzer';
    case ModelChatAgent = 'ModelChatAgent';

    case GuidelineRecommender = 'GuidelineRecommender';
    case StructuredOutput = 'StructuredOutput';
}
