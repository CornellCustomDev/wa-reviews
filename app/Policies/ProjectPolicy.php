<?php

namespace App\Policies;

use App\Enums\Permissions;
use App\Enums\ProjectStatus;
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
            || ($project->isReportViewer($user) && ($project->hasBeenReviewed() || $project->isClosed()));
    }

    public function create(User $user, Team $team): bool
    {
        return $user->can('create-projects', $team);
    }

    public function update(User $user, Project $project): bool
    {
        // Projects cannot be updated when they are closed
        if ($project->isClosed()) {
            return false;
        }

        // If this is the reviewer's project, they can update it
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team);
    }

    public function updateReviewer(User $user, Project $project, ?User $reviewer = null): bool
    {
        // Reviewer can only be set while the project is active.
        if (! $project->isActive()) {
            return false;
        }

        // If this is the reviewer's project, they can assign it to another reviewer.
        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
            return true;
        }

        // If there is no reviewer, the current user can assign to self.
        if (is_null($project->reviewer)) {
            if ($reviewer && $reviewer->id == $user->id) {
                return $user->can('create-projects', $project->team);
            }
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateVerifier(User $user, Project $project, ?User $verifier = null): bool
    {
        // Only projects in review can update the verifier
        if (! $project->hasBeenReviewed()) {
            return false;
        }

        // If this is the verifier's project, they can assign it to another verifier.
        if ($project->isVerifier($user) && $user->can('edit-projects', $project->team)) {
            return true;
        }

        // If there is no verifier, the current user can assign to self.
        if (is_null($project->verifier)) {
            if ($verifier && $verifier->id === $user->id) {
                return $user->can('edit-projects', $project->team);
            }
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        // If this is the user's project, they can update the status if it's in progress
        if ($project->isReviewer($user) || $project->isVerifier($user)) {
            return $user->can('edit-projects', $project->team);
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateReport(User $user, Project $project): bool
    {
        // Reviewers can update the report if it is in progress
        if ($project->isReviewer($user) && $project->isInProgress()) {
            return $user->can('edit-projects', $project->team);
        }

        return $user->can('manage-projects', $project->team);
    }

    public function completeReport(User $user, Project $project): bool
    {
        if (! $project->isReportReady()) {
            return false;
        }

        // Reviewers can complete the report if it is in progress
        if ($project->isReviewer($user)) {
            return $user->can('edit-projects', $project->team);
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateReportViewers(User $user, Project $project): bool
    {
        // Report viewers cannot be added to projects that haven't started yet
        if ($project->isNotStarted()) {
            return false;
        }

        // If this is the user's project, they can update report viewers
        if ($project->isReviewer($user) || $project->isVerifier($user)) {
            return $user->can('edit-projects', $project->team);
        }

        return $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Project $project): bool
    {
        // Only administrators can delete projects after they are reviewed
        if ($project->hasBeenReviewed() || $project->isClosed()) {
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
