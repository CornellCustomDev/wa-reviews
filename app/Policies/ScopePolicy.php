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
            || $project->isReportViewer($user)
            || $project->canManageProject($user);
    }

    public function create(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        return ($project->isInProgress() && $project->isReviewer($user))
            || $project->canManageProject($user);
    }

    public function update(User $user, Scope $scope): bool
    {
        $project = $scope->project;
        if ($project->isCompleted()) {
            return false;
        }

        return ($project->isInProgress() && $project->isReviewer($user))
            || $project->canManageProject($user);
    }

    public function delete(User $user, Scope $scope): bool
    {
        $project = $scope->project;
        if ($project->isCompleted()) {
            return false;
        }

        return $project->isReviewer($user)
            || $project->canManageProject($user);
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
