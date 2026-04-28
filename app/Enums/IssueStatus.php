<?php

namespace App\Enums;

use Illuminate\Support\Str;

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
            self::WontFix => 'Not being fixed',
            default       => Str::of($this->value())->replace('_', ' ')->ucfirst(),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Reviewed => '',
            self::Fixed    => '🛠️ ' . $this->label(),
            self::Verified => '✅ ' . $this->label(),
            self::WontFix  => '🚫 ' . $this->label(),
            default        => $this->label(),
        };
    }
}
