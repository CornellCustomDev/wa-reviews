<?php

namespace App\Enums;

use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

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
        if (! Feature::active('verification-reviews') && ($this->hasBeenReviewed() || $this->isClosed())) {
            return 'Completed';
        }

        return Str::of($this->value())->replace('_', ' ')->ucfirst();
    }

    public function nextStatus(): ProjectStatus
    {
        if (! Feature::active('verification-reviews') && ($this->isInProgress() || $this->hasBeenReviewed() || $this->isClosed())) {
            return self::Closed;
        }

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
        if (! Feature::active('verification-reviews') && ($this->hasBeenReviewed() || $this->isClosed())) {
            return self::InProgress;
        }

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
        if (! Feature::active('verification-reviews')) {
            if ($this->isInProgress()) {
                return 'If the work is finished, mark it as completed in order to make it available in a read-only view.';
            }
            if ($this->hasBeenReviewed() || $this->isClosed()) {
                return 'The review is complete, but you can re-open it if you need to make changes.';
            }
        }

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
        if (! Feature::active('verification-reviews') && ($this->hasBeenReviewed() || $this->isClosed())) {
            return null;
        }

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
        if (! Feature::active('verification-reviews') && ($this->hasBeenReviewed() || $this->isClosed())) {
            return 'Re-open Review';
        }

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
        if (! Feature::active('verification-reviews')) {
            return $this === self::Closed;
        }

        return $this === self::ReviewComplete;
    }

    public function hasBeenReviewed(): bool
    {
        if (! Feature::active('verification-reviews')) {
            return $this === self::Closed;
        }

        return in_array($this, self::reviewedCases());
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
        if (! Feature::active('verification-reviews')) {
            return [...self::reviewedCases(), self::Closed];
        }

        return [self::Closed];
    }
}
