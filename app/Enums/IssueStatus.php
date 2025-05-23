<?php

namespace App\Enums;

enum IssueStatus: string
{
    use NamedEnum;

    case Reviewed = 'Reviewed';
    case Fixed = 'Fixed';
    case Verified = 'Verified Fixed';
    case FalsePositive = 'False Positive';
    case WontFix = 'Not Being Fixed';

    public static function toSelectArray(): array
    {
        return collect(self::cases())
            ->map(fn (self $status) => [
                'value' => $status->value(),
                'option' => $status->value(),
            ])
            ->toArray();
    }

    public function description(): string
    {
        return match ($this) {
            self::Reviewed => '', // Don't show anything
            self::Fixed => '🛠️ Fixed',
            self::Verified => '✅ Verified',
            self::FalsePositive => 'False positive',
            self::WontFix => 'Not being fixed',
        };
    }
}
