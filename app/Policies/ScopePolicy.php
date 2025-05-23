<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Project;
use App\Models\Scope;
use App\Models\User;

class ScopePolicy
{
    private function isProjectAdministrator(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $project->team)
            || $user->isAdministrator();
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Scope $scope): bool
    {
        $project = $scope->project;

        return $project->team->isTeamMember($user)
            || $project->isReportViewer($user)
            || $this->isProjectAdministrator($user, $project);
    }

    public function create(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        return ($project->isInProgress() && $project->isProjectReviewer($user))
            || $this->isProjectAdministrator($user, $project);
    }

    public function update(User $user, Scope $scope): bool
    {
        $project = $scope->project;
        if ($project->isCompleted()) {
            return false;
        }

        return ($project->isInProgress() && $project->isProjectReviewer($user))
            || $this->isProjectAdministrator($user, $project);
    }

    public function delete(User $user, Scope $scope): bool
    {
        $project = $scope->project;
        if ($project->isCompleted()) {
            return false;
        }

        return $project->isProjectReviewer($user)
            || $this->isProjectAdministrator($user, $project);
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
