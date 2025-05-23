<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->teams()->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $project->team->isTeamMember($user)
            || $project->isReportViewer($user)
            || $project->canManageProject($user);
    }

    public function manageProject(User $user, Project $project): bool
    {
        return $project->canManageProject($user);
    }

    public function create(User $user, Team $team): bool
    {
        // TODO: Create a permission for CreateTeamProjects. and add to reviewers
        if ($team->isReviewer($user)) {
            return true;
        }

        return $user->isAbleTo(Permissions::ManageTeamProjects, $team)
            || $user->isAbleTo(Permissions::ManageTeams);
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        if ($user->id == $project->reviewer?->id) {
            return $project->isInProgress();
        }

        return $project->canManageProject($user);
    }

    public function updateReviewer(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        return $project->canManageProject($user);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        if ($user->id == $project->reviewer?->id) {
            return $project->isInProgress() || $project->isCompleted();
        }

        return $project->canManageProject($user);
    }

    public function delete(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            // Completed projects cannot be deleted except by a site administrator
            return $user->isAdministrator();
        }

        return $project->canManageProject($user);
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
