<?php

namespace App\Enums;

enum IssueStatus: string
{
    use NamedEnum;

    case Reviewed = 'Reviewed';
    case Fixed = 'Fixed';
    case NotBeingFixed = 'Not Being Fixed';
    case FalsePositive = 'False Positive';
    case Verified = 'Verified Fixed';
    case NotFixed = 'Not Fixed';
    case NewIssue = 'New Issue';

    public static function toSelectArray(): array
    {
        return collect(self::cases())
            ->map(fn (self $status) => [
                'value' => $status->value(),
                'option' => $status->value(),
            ])
            ->toArray();
    }

    public static function forPhase(ProjectStatus $projectStatus): array
    {
        $cases = match ($projectStatus) {
            ProjectStatus::ReviewComplete => [self::Reviewed, self::FalsePositive],
            ProjectStatus::CustomerResponse => [self::Reviewed, self::Fixed, self::NotBeingFixed, self::FalsePositive],
            ProjectStatus::VerificationReview => [self::Verified, self::NotFixed, self::FalsePositive, self::NotBeingFixed],
            default => self::cases(),
        };

        return collect($cases)
            ->map(fn (self $status) => [
                'value' => $status->value(),
                'option' => $status->value(),
            ])
            ->toArray();
    }

    public function description(): string
    {
        return match ($this) {
            self::Reviewed => '',
            self::Fixed => '🛠️ Fixed',
            self::NotBeingFixed => 'Not being fixed',
            self::FalsePositive => 'False positive',
            self::Verified => '✅ Verified Fixed',
            self::NotFixed => '❌ Not Fixed',
            self::NewIssue => 'New Issue',
        };
    }
}
