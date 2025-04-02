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
        return $scope->project->team->isTeamMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        return ($project->isInProgress() && $project->isProjectReviewer($user))
            || $user->isAbleTo(Permissions::ManageTeamProjects, $project->team);
    }

    public function update(User $user, Scope $scope): bool
    {
        /** @var Project $project */
        $project = $scope->project;
        return ($project->isInProgress() && $project->isProjectReviewer($user))
            || $user->isAbleTo(Permissions::ManageTeamProjects, $project->team);
    }

    public function delete(User $user, Scope $scope): bool
    {
        return ($scope->project->isInProgress() && $scope->project->isProjectReviewer($user))
            || $user->isAbleTo(Permissions::ManageTeamProjects, $scope->project->team);
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
