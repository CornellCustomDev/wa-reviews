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
            || $project->isReportViewer($user);
    }

    public function manageProject(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $project->team);
    }

    public function create(User $user, ?Team $team = null): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $team);
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        return $user->isAbleTo(Permissions::ManageTeamProjects, $project->team)
            || ($user->id == $project->reviewer->id && ($project->isInProgress()));
    }

    public function updateReviewer(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $project->team) && ! $project->isCompleted();
    }

    public function updateStatus(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $project->team)
            || ($user->id == $project->reviewer->id && ($project->isInProgress() || $project->isCompleted()));
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAbleTo(Permissions::ManageTeamProjects, $project->team);
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
