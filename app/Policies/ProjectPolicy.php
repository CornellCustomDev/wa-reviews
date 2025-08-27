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
        if ($user->isAbleTo(Permissions::ManageTeams) || $user->teams()->exists()) {
            return true;
        }

        // If the user is a report viewer, they can view projects
        return Project::reportViewerProjects($user)->exists();
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

        // If this is the reviewer's project, they can update it
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team);
    }

    public function updateReviewer(User $user, Project $project, ?User $reviewer = null): bool
    {
        // No updating the reviewer if the project is completed
        if ($project->isCompleted()) {
            return false;
        }

        // If this is the reviewer's project, they can assign it to another reviewer
        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
            return true;
        }

        // If there is no reviewer, the user can assign to self
        if (is_null($project->reviewer)) {
            // Assigning to the current user
            if ($reviewer && $reviewer->id == $user->id) {
                return $user->can('create-projects', $project->team);
            }
        }

        // Otherwise, the user must have permission to manage projects for the team
        return $user->can('manage-projects', $project->team);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        // If this is the reviewer's project, they can update the status if it's in progress
        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
            return true;
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
        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
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
