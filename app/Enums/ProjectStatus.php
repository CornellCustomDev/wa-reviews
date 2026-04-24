<?php

namespace App\Enums;

enum ProjectStatus: string
{
    use NamedEnum;

    case NotStarted = 'Not Started';
    case InProgress = 'In Progress';
    case ReviewComplete = 'Review Complete';
    case CustomerResponse = 'Customer Response';
    case VerificationReview = 'Verification Review';
    case Closed = 'Closed';

    public function nextStatus(): ProjectStatus
    {
        return match ($this) {
            self::NotStarted => self::InProgress,
            self::InProgress => self::ReviewComplete,
            self::ReviewComplete => self::CustomerResponse,
            self::CustomerResponse => self::VerificationReview,
            self::VerificationReview => self::Closed,
            self::Closed => self::Closed,
        };
    }

    public function previousStatus(): ProjectStatus
    {
        return match ($this) {
            self::InProgress => self::NotStarted,
            self::ReviewComplete => self::InProgress,
            self::CustomerResponse => self::ReviewComplete,
            self::VerificationReview => self::CustomerResponse,
            self::Closed => self::VerificationReview,
            self::NotStarted => self::NotStarted,
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

    public function isReviewComplete(): bool
    {
        return $this === self::ReviewComplete;
    }

    public function isCustomerResponse(): bool
    {
        return $this === self::CustomerResponse;
    }

    public function isVerificationReview(): bool
    {
        return $this === self::VerificationReview;
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }

    public function isCompleted(): bool
    {
        return $this === self::Closed;
    }

    public function isPostReview(): bool
    {
        return in_array($this, [...self::reviewedCases(), ...self::completedCases()]);
    }

    public static function activeCases(): array
    {
        return [self::NotStarted, self::InProgress];
    }

    public static function reviewedCases(): array
    {
        return [self::ReviewComplete, self::CustomerResponse, self::VerificationReview];
    }

    public static function completedCases(): array
    {
        return [self::Closed];
    }
}
