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
            || $project->isReportViewer($user)
            || $project->canManageProject($user);
    }

    public function create(User $user, Project $project): bool
    {
        if ($project->isCompleted()) {
            return false;
        }

        return ($project->isInProgress() && $project->isReviewer($user))
            || $project->canManageProject($user);
    }

    public function update(User $user, Issue $issue): bool
    {
        $project = $issue->project;
        if ($project->isCompleted()) {
            return false;
        }

        return ($project->isInProgress() && $project->isReviewer($user))
            || $project->canManageProject($user);
    }

    public function updateStatus(User $user, Issue $issue): bool
    {
        $project = $issue->project;
        if (! $project->isCompleted()) {
            return false;
        }

        return $project->isReviewer($user)
            || $project->isReportViewer($user)
            || $project->canManageProject($user);

    }

    public function updateNeedsMitigation(User $user, Issue $issue): bool
    {
        $project = $issue->project;
        if (! $project->isCompleted()) {
            return false;
        }

        return $project->isReviewer($user)
            || $project->canManageProject($user);
    }

    public function delete(User $user, Issue $issue): bool
    {
        $project = $issue->project;
        if ($project->isCompleted()) {
            return false;
        }

        return $project->isReviewer($user)
            || $project->canManageProject($user);
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
