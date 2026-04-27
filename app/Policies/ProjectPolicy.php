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

        return Project::reportViewerProjects($user)->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $project->team->isTeamMember($user)
            || $user->can('manage-projects', $project->team)
            || ($project->isReportViewer($user) && $project->isPostReview());
    }

    public function create(User $user, Team $team): bool
    {
        return $user->can('create-projects', $team);
    }

    public function update(User $user, Project $project): bool
    {
        if ($project->isPostReview()) {
            return false;
        }

        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team);
    }

    public function updateReviewer(User $user, Project $project, ?User $reviewer = null): bool
    {
        if (! in_array($project->status, ProjectStatus::activeCases())) {
            return false;
        }

        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
            return true;
        }

        if (is_null($project->reviewer)) {
            if ($reviewer && $reviewer->id == $user->id) {
                return $user->can('create-projects', $project->team);
            }
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateVerifier(User $user, Project $project, ?User $verifier = null): bool
    {
        if (! in_array($project->status, ProjectStatus::reviewedCases())) {
            return false;
        }

        if ($project->isVerifier($user)) {
            return true;
        }

        if (is_null($project->verifier)) {
            if ($verifier && $verifier->id === $user->id) {
                return $user->can('edit-projects', $project->team);
            }
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateStatus(User $user, Project $project): bool
    {
        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
            return true;
        }

        return $user->can('manage-projects', $project->team);
    }

    public function updateReportViewers(User $user, Project $project): bool
    {
        if (! $project->isPostReview()) {
            return false;
        }

        if ($project->isReviewer($user) && $user->can('edit-projects', $project->team)) {
            return true;
        }

        return $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Project $project): bool
    {
        if ($project->isPostReview()) {
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
