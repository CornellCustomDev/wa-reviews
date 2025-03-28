<?php

namespace App\Enums;

enum Permissions: string
{
    use NamedEnum;

    // Administrative
    case ManageSiteConfig = 'manage site config';
    case ManageTeams = 'manage teams';

    // Teams
    case ManageTeamMembers = 'manage team members';
    case ManageTeamProjects = 'manage team projects';

    // Members
    case EditProjects = 'edit projects';
}
