<?php

namespace App\Enums;

use Illuminate\Support\Arr;

enum AIWisdom: string
{
    /**
     * Wisdom words for AI-related tasks.
     */

    case REVIEW = 'Always review AI responses before use.';
    case CHECK = 'Always check AI answers for accuracy.';
    case JUDGMENT = 'Always use judgment with AI responses.';
    case READ = 'Read carefully. Decide for yourself.';
    case VERIFY = 'AI makes mistake. Trust but verify.';
    case CAUTION = 'Caution: AI may not be accurate.';

    public static function getOne(): string
    {
        return Arr::random(self::cases())->value;
    }
}
