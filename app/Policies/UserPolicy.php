<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function view(User $user, User $model): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function create(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
