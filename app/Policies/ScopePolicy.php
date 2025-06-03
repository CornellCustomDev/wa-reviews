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
        $project = $scope->project;

        return $project->team->isTeamMember($user)
            || ($project->isReportViewer($user) && $project->isCompleted())
            || $user->can('manage-projects', $project->team);
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
        $project = $scope->project;

        // Scopes can only be deleted if the user can update the project
        if ($user->cannot('update', $project)) {
            return false;
        }

        // Scopes can only be deleted by the reviewer or a team manager
        return $project->isReviewer($user)
            || $user->can('manage-projects', $project->team);
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
