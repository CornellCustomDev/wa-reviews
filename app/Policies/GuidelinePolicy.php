<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Guideline;
use App\Models\User;

class GuidelinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function view(User $user, Guideline $guideline): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function update(User $user, Guideline $guideline): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function delete(User $user, Guideline $guideline): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function restore(User $user, Guideline $guideline): bool
    {
        return false;
    }

    public function forceDelete(User $user, Guideline $guideline): bool
    {
        return false;
    }
}
