<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Project;
use App\Models\Scope;
use App\Models\User;

class ScopePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Scope $scope): bool
    {
        return $user->isTeamMember($scope->project->team);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $project->team);
    }

    public function update(User $user, Scope $scope): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $scope->project->team);
    }

    public function delete(User $user, Scope $scope): bool
    {
        return $user->isAbleTo(Permissions::EditProjects, $scope->project->team);
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
