<?php

namespace App\Policies;

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
            || $user->can('manage-projects', $project->team)
            || ($project->isReportViewer($user) && $project->isCompleted());
    }

    public function create(User $user, Team $team): bool
    {
        return $user->can('create-projects', $team);
    }

    public function update(User $user, Project $project): bool
    {
        // Projects cannot be updated while they have a status of "completed"
        if ($project->isCompleted()) {
            return false;
        }

        // Projects can be updated only if someone is able to edit projects for the team
        if ($user->cannot('edit-projects', $project->team)) {
            return false;
        }

        // If this is the reviewer's project, they can update it while it's in progress
        if ($project->isReviewer($user)) {
            return $project->isInProgress();
        }

        // Otherwise, the user must have permission to manage projects for the team
        return $user->can('manage-projects', $project->team);
    }

    public function updateReviewer(User $user, Project $project): bool
    {
        // No updating the reviewer if the project is completed
        if ($project->isCompleted()) {
            return false;
        }

        // If this is the reviewer's project, they can assign it to another reviewer
        if ($project->isReviewer($user)) {
            return true;
        }

        // If there is no reviewer, the user can assign the review if they have permission
        if (is_null($project->reviewer)) {
            return $user->can('create-projects', $project->team);
        }

        // Otherwise, the user must have permission to manage projects for the team
        return $user->can('manage-projects', $project->team);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        // If this is the reviewer's project, they can update the status if it's in progress
        if ($project->isReviewer($user)) {
            return $project->isInProgress() || $project->isCompleted();
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateReportViewers(User $user, Project $project): bool
    {
        // Report viewers are only managed for completed projects
        if (! $project->isCompleted()) {
            return false;
        }

        // If this is the reviewer's project, they can update report viewers
        if ($project->isReviewer($user)) {
            return true;
        }

        // Otherwise, the user must have permission to manage projects for the team
        return $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            // Completed projects cannot be deleted except by a site administrator
            return $user->isAdministrator();
        }

        return $user->can('manage-projects', $project->team);
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
