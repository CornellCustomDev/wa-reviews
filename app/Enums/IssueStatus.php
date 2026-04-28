<?php

namespace App\Enums;

enum IssueStatus: string
{
    use NamedEnum;

    case Reviewed      = 'reviewed';
    case Fixed         = 'fixed';
    case Verified      = 'verified_fixed';
    case FalsePositive = 'false_positive';
    case WontFix       = 'not_being_fixed';

    public function label(): string
    {
        return match ($this) {
            self::Reviewed      => 'Reviewed',
            self::Fixed         => 'Fixed',
            self::Verified      => 'Verified Fixed',
            self::FalsePositive => 'False Positive',
            self::WontFix       => 'Not Being Fixed',
        };
    }

    public static function toSelectArray(): array
    {
        return collect(self::cases())
            ->map(fn (self $status) => [
                'value' => $status->value(),
                'option' => $status->label(),
            ])
            ->toArray();
    }

    public function description(): string
    {
        return match ($this) {
            self::Reviewed      => '',
            self::Fixed         => '🛠️ Fixed',
            self::Verified      => '✅ Verified',
            self::FalsePositive => 'False positive',
            self::WontFix       => 'Not being fixed',
        };
    }
}
