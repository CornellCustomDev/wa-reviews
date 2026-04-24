<?php

namespace App\Policies;

use App\Enums\ProjectStatus;
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
        return $user->can('update', $project);
    }

    public function update(User $user, Issue $issue): bool
    {
        return $user->can('update', $issue->project);
    }

    public function updateStatus(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        return match ($project->status) {
            ProjectStatus::ReviewComplete => $this->canUpdateStatusInReviewComplete($user, $project),
            ProjectStatus::CustomerResponse => $this->canUpdateStatusInCustomerResponse($user, $project),
            ProjectStatus::VerificationReview => $this->canUpdateStatusInVerificationReview($user, $project),
            default => false,
        };
    }

    public function updateNeedsMitigation(User $user, Issue $issue): bool
    {
        $project = $issue->project;

        if (! $project->isPostReview()) {
            return false;
        }

        if ($user->cannot('edit-projects', $project->team)) {
            return false;
        }

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

    private function canUpdateStatusInReviewComplete(User $user, Project $project): bool
    {
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $user->can('manage-projects', $project->team)
            || $user->isAdministrator();
    }

    private function canUpdateStatusInCustomerResponse(User $user, Project $project): bool
    {
        return $project->isReportViewer($user)
            || $user->can('manage-projects', $project->team)
            || $user->isAdministrator();
    }

    private function canUpdateStatusInVerificationReview(User $user, Project $project): bool
    {
        return ($project->isReviewer($user) && $user->can('edit-projects', $project->team))
            || $project->isVerifier($user)
            || $user->can('manage-projects', $project->team)
            || $user->isAdministrator();
    }
}
