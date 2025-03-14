<?php

namespace App\Enums;

enum Roles: string
{
    use NamedEnum;

    case SuperAdmin = 'super admin';
    case ProjectManager = 'project manager';
    case User = 'user';
}
