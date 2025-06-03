<?php

namespace App\Enums;

enum Roles: string
{
    use NamedEnum;

    case SiteAdmin = 'site admin';
    case TeamAdmin = 'team admin';
    case Reviewer = 'reviewer';

    public static function getTeamRoles(): array
    {
        return [
            self::TeamAdmin->value,
            self::Reviewer->value,
        ];
    }

    public static function getRolePermissions($role): array
    {
        return match ($role) {
            self::SiteAdmin->value => [
                Permissions::ManageSiteConfig->value,
                Permissions::ManageTeams->value,
                Permissions::ManageTeamMembers->value,
                Permissions::ManageTeamProjects->value,
                Permissions::CreateTeamProjects->value,
                Permissions::EditProjects->value,
            ],
            self::TeamAdmin->value => [
                Permissions::ManageTeamMembers->value,
                Permissions::ManageTeamProjects->value,
                Permissions::CreateTeamProjects->value,
                Permissions::EditProjects->value,
            ],
            self::Reviewer->value => [
                Permissions::CreateTeamProjects->value,
                Permissions::EditProjects->value,
            ],
            default => [],
        };
    }
}
