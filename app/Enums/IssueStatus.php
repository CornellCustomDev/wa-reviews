<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum IssueStatus: string
{
    use NamedEnum;

    case Reviewed      = 'reviewed';
    case Fixed         = 'fixed';
    case WontFix       = 'not_being_fixed';
    case FalsePositive = 'false_positive';
    case Verified      = 'verified_fixed';
    case NotFixed      = 'not_fixed';
    case NewIssue      = 'new_issue';

    public function label(): string
    {
        return match ($this) {
            self::WontFix => 'Not being fixed',
            default       => Str::of($this->value())->replace('_', ' ')->ucfirst(),
        };
    }

    public static function forPhase(ProjectStatus $projectStatus): array
    {
        $cases = match ($projectStatus) {
            ProjectStatus::ReviewComplete => [self::Reviewed, self::FalsePositive],
            ProjectStatus::CustomerResponse => [self::Reviewed, self::Fixed, self::WontFix, self::FalsePositive],
            ProjectStatus::VerificationReview => [self::Reviewed, self::Verified, self::NotFixed, self::FalsePositive, self::WontFix],
            default => self::cases(),
        };

        return collect($cases)
            ->map(fn (self $status) => [
                'value' => $status->value(),
                'option' => $status->label(),
            ])
            ->toArray();
    }

    public function description(): string
    {
        return match ($this) {
            self::Reviewed => '',
            self::Fixed    => '🛠️ ' . $this->label(),
            self::Verified => '✅ ' . $this->label(),
            self::WontFix  => '🚫 ' . $this->label(),
            self::NotFixed => '❌ ' . $this->label(),
            default        => $this->label(),
        };
    }
}
