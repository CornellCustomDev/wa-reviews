<?php

namespace App\Enums;

enum Roles: string
{
    use NamedEnum;

    case SiteAdmin = 'site admin';
    case TeamAdmin = 'team admin';
    case Reviewer = 'reviewer';
    case Member = 'member';

    public static function getTeamRoles(): array
    {
        return [
            self::TeamAdmin->value,
            self::Reviewer->value,
            self::Member->value,
        ];
    }
}
