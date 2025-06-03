<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        return $project->team->isTeamMember($user)
            || ($project->isReportViewer($user) && $project->isCompleted())
            || $user->can('manage-projects', $project->team);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->can('update', $project);
    }

    public function update(User $user, Issue $issue): bool
    {
        return $user->can('update', $issue->project);
    }

    public function updateStatus(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        // Issue status updates are only for completed projects
        if (! $project->isCompleted()) {
            return false;
        }

        // Status updates can only be made by the reviewer, report viewer, or a team manager
        return $project->isReviewer($user)
            || $project->isReportViewer($user)
            || $user->can('manage-projects', $project->team);
    }

    public function updateNeedsMitigation(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        // Mitigation updates are only for completed projects
        if (! $project->isCompleted()) {
            return false;
        }

        if ($user->cannot('edit-projects', $project->team)) {
            return false;
        }

        // Mitigation updates can only be made by the reviewer or a team manager
        return $project->isReviewer($user)
            || $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        // Issues can only be deleted if the user can update the project
        if ($user->cannot('update', $project)) {
            return false;
        }

        // Issues can only be deleted by the reviewer or a team manager
        return $project->isReviewer($user)
            || $user->can('manage-projects', $project->team);
    }

    public function restore(User $user, Issue $issue): bool
    {
        return false;
    }

    public function forceDelete(User $user, Issue $issue): bool
    {
        return false;
    }
}
