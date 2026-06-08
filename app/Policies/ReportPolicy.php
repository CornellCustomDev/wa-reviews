<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Report $report): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Report $report): bool
    {
        // Reviewers can update the report if the project is in progress
        $project = $report->project;
        if ($project->isReviewer($user) && $project->isInProgress()) {
            return $user->can('edit-projects', $project->team);
        }

        return $user->can('manage-projects', $project->team);
    }

    public function completeReport(User $user, Report $report): bool
    {
        $project = $report->project;
        if (! ($project->isInProgress() && $report->isReady())) {
            return false;
        }

        // Reviewers can complete the report if it is in progress
        if ($project->isReviewer($user)) {
            return $user->can('edit-projects', $project->team);
        }

        return $user->can('manage-projects', $project->team);
    }

    public function delete(User $user, Report $report): bool
    {
        return false;
    }

    public function restore(User $user, Report $report): bool
    {
        return false;
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return false;
    }
}
