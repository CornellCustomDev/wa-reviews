<?php

namespace App\Enums;

enum Impact: string
{
    use NamedEnum;

    case Critical = 'Critical';
    case Serious = 'Serious';
    case Moderate = 'Moderate';
    case Low = 'Low';

    public function getDescription(): string
    {
        return match ($this) {
            self::Critical => 'A severe barrier that prevents users with affected disabilities from being able to complete primary tasks or access main content.',
            self::Serious => 'A barrier that will make task completion or content access significantly more difficult and time consuming for individuals with affected disabilities, or that may prevent affected users from completing secondary tasks or accessing supplemental content without outside support.',
            self::Moderate => 'A barrier that will make it somewhat more difficult for users with affected disabilities to complete central or secondary tasks or access content.',
            self::Low => 'A barrier that has the potential to force users with affected disabilities to use mildly inconvenient workarounds, but that does not cause much, if any, difficulty completing tasks or accessing content.',
        };
    }
}
