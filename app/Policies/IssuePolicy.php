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
        return $user->can('view', $issue->project);
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
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
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
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $user->can('update', $issue->project);
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
