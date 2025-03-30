<?php

namespace App\Enums;

enum ProjectStatus: string
{
    use NamedEnum;

    case NotStarted = 'Not Started';
    case InProgress = 'In Progress';
    case Completed  = 'Completed';
//    case OnHold     = 'On Hold';
//    case Cancelled  = 'Cancelled';
//    case Archived   = 'Archived';

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

    public function isInProgress(): bool
    {
        return $this === self::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this === self::Completed;
    }
}
