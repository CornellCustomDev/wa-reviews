<?php

namespace App\Policies;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\Project;
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
        if ($project->isInVerification()) {
            return $project->isVerifier($user) && $user->can('edit-projects', $project->team);
        }

        return $project->isInProgress() && $user->can('update', $project);
    }

    public function update(User $user, Issue $issue): bool
    {
        return $issue->project->isInProgress() && $user->can('update', $issue->project);
    }

    public function updateStatus(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        // Only reviewed projects that are open
        if ($project->isClosed() || ! $project->hasBeenReviewed()) {
            return false;
        }

        // Report viewers can update status if the project is not in verification
        if ($project->isReportViewer($user)) {
            return ! $project->isInVerification();
        }

        // Updates can only be made by the reviewer, verifier, or a team manager
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || ($project->isVerifier($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team);
    }

    public function updateNeedsMitigation(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        // Only reviewed projects that are open
        if ($project->isClosed() || ! $project->hasBeenReviewed()) {
            return false;
        }

        // Updates can only be made by the reviewer, verifier, or a team manager
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || ($project->isVerifier($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Issue $issue): bool
    {
        if ($issue->project->isInVerification() && $issue->status == IssueStatus::NewIssue) {
            return $issue->project->isVerifier($user) && $user->can('edit-projects', $issue->project->team);
        }

        // Issues can be deleted if the project is active.
        return $issue->project->isActive() && $user->can('update', $issue->project);
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
