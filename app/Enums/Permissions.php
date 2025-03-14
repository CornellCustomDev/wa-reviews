<?php

namespace App\Enums;

enum Permissions: string
{
    use NamedEnum;

    // Administrative
    case ManageSiteConfig = 'manage site config';
    case ManageUsers = 'manage users';

    // Projects
    case ManageProjects = 'manage projects';
}
