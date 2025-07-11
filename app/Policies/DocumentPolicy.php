<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function view(User $user, Document $document): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function update(User $user, Document $document): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->isAbleTo(Permissions::ManageSiteConfig);
    }

    public function restore(User $user, Document $document): bool
    {
        return false;
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return false;
    }
}
