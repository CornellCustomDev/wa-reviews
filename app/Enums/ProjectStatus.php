<?php

namespace App\Enums;

use Illuminate\Support\Str;

enum ProjectStatus: string
{
    use NamedEnum;

    case NotStarted = 'not_started';
    case InProgress = 'in_progress';
    case Completed  = 'completed';

    public function label(): string
    {
        return Str::of($this->value())->replace('_', ' ')->ucfirst();
    }

    public function nextStatus(): ProjectStatus
    {
        return match ($this) {
            self::NotStarted => self::InProgress,
            self::InProgress => self::Completed,
            self::Completed  => self::Completed,
        };
    }

    public function previousStatus(): ProjectStatus
    {
        return match ($this) {
            self::InProgress => self::NotStarted,
            self::Completed  => self::InProgress,
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

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }

    public static function activeCases(): array
    {
        return [
            self::NotStarted,
            self::InProgress,
        ];
    }

    public static function completedCases(): array
    {
        return [
            self::Completed,
        ];
    }
}
