<?php

namespace App\Enums;

use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

enum IssueStatus: string
{
    use NamedEnum;

    case Reviewed      = 'reviewed';
    case Fixed         = 'fixed';
    case FalsePositive = 'false_positive';
    case WontFix       = 'not_being_fixed';
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
            ProjectStatus::ReviewComplete => [
                self::Reviewed,
                self::Fixed,
                ! Feature::active('verification-reviews') ? self::Verified : null,
                self::FalsePositive,
                self::WontFix
            ],
            ProjectStatus::VerificationReview => [self::Verified, self::NotFixed, self::NewIssue, self::Reviewed, self::Fixed, self::FalsePositive, self::WontFix],
            default => self::cases(),
        };

        return collect($cases)
            ->filter()
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
            self::Verified => '✅ ' . $this->label(),
            self::WontFix  => '🚫 ' . $this->label(),
            self::NotFixed => '❌ ' . $this->label(),
            self::NewIssue => '🆕 ' . $this->label(),
            default        => $this->label(),
        };
    }
}
