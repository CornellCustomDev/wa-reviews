<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class ProjectPolicy
{
    private function isProjectAdministrator(User $user, ?Team $team = null): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $team)
            || $user->isAdministrator();
    }

    public function viewAny(User $user): bool
    {
        return $user->teams()->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $project->team->isTeamMember($user)
            || $project->isReportViewer($user)
            || $this->isProjectAdministrator($user, $project->team);
    }

    public function manageProject(User $user, Project $project): bool
    {
        return $this->isProjectAdministrator($user, $project->team);
    }

    public function create(User $user, ?Team $team = null): bool
    {
        return $this->isProjectAdministrator($user, $team);
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        if ($user->id == $project->reviewer?->id) {
            return $project->isInProgress();
        }

        return $this->isProjectAdministrator($user, $project->team);
    }

    public function updateReviewer(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        return $this->isProjectAdministrator($user, $project->team);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        if ($user->id == $project->reviewer?->id) {
            return $project->isInProgress() || $project->isCompleted();
        }

        return $this->isProjectAdministrator($user, $project->team);
    }

    public function delete(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            // Completed projects cannot be deleted except by a site administrator
            return $user->isAdministrator();
        }

        return $this->isProjectAdministrator($user, $project->team);
    }

    public function restore(User $user, Project $project): bool
    {
        return false;
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return false;
    }
}
