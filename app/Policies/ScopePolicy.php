<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Scope;
use App\Models\User;

class ScopePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Scope $scope): bool
    {
        return $user->can('view', $scope->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->can('update', $project);
    }

    public function update(User $user, Scope $scope): bool
    {
        return $user->can('update', $scope->project);
    }

    public function delete(User $user, Scope $scope): bool
    {
        return $user->can('update', $scope->project);
    }

    public function restore(User $user, Scope $scope): bool
    {
        return false;
    }

    public function forceDelete(User $user, Scope $scope): bool
    {
        return false;
    }
}
