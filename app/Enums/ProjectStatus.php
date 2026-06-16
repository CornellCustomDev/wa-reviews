<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum ProjectStatus: string
{
    use NamedEnum;

    case NotStarted         = 'not_started';
    case InProgress         = 'in_progress';
    case ReviewComplete     = 'review_complete';
    case VerificationReview = 'verification_review';
    case Closed             = 'closed';

    public function label(): string
    {
        return Str::of($this->value())->replace('_', ' ')->ucfirst();
    }

    public function nextStatus(): ProjectStatus
    {
        return match ($this) {
            self::NotStarted => self::InProgress,
            self::InProgress => self::ReviewComplete,
            self::ReviewComplete => self::VerificationReview,
            self::VerificationReview,
            self::Closed => self::Closed,
        };
    }

    public function previousStatus(): ProjectStatus
    {
        return match ($this) {
            self::NotStarted,
            self::InProgress => self::NotStarted,
            self::ReviewComplete => self::InProgress,
            self::VerificationReview => self::ReviewComplete,
            self::Closed => self::VerificationReview,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::NotStarted => 'No reviewer has been assigned to this project. Are you sure you want to start the review?',
            self::InProgress => 'When the review is finished, mark it as complete.',
            self::ReviewComplete => 'The review is complete. Work should be verified after fixes have been applied.',
            self::VerificationReview => 'When verification is finished, mark it as complete.',
            self::Closed => 'The review and verification is complete. You can re-open it if needed.',
        };
    }

    public function nextActionLabel(): ?string
    {
        return match ($this) {
            self::NotStarted => 'Start Review',
            self::InProgress => 'Complete Review',
            self::ReviewComplete => 'Start Verification',
            self::VerificationReview => 'Complete Verification',
            self::Closed => null,
        };
    }

    public function previousActionLabel(): ?string
    {
        return match ($this) {
            self::NotStarted => null,
            self::InProgress => 'Stop Review',
            self::ReviewComplete => 'Re-open Review',
            self::VerificationReview => 'Pause Verification',
            self::Closed => 'Re-open',
        };
    }

    public function isNotStarted(): bool
    {
        return $this === self::NotStarted;
    }

    public function isInProgress(): bool
    {
        return $this === self::InProgress;
    }

    public function isActive(): bool
    {
        return in_array($this, self::activeCases());
    }

    public function isReviewComplete(): bool
    {
        return $this === self::ReviewComplete;
    }

    public function isInVerification(): bool
    {
        return $this === self::VerificationReview;
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }

    public static function activeCases(): array
    {
        return [self::NotStarted, self::InProgress];
    }

    public static function reviewedCases(): array
    {
        return [self::ReviewComplete, self::VerificationReview];
    }

    public static function closedCases(): array
    {
        return [self::Closed];
    }
}
