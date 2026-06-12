<?php

namespace App\Services;

use App\Enums\ProjectStatus;
use App\Events\ProjectChanged;
use App\Models\Project;

class ProjectWorkflowService
{
    public function advance(Project $project): void
    {
        $nextStatus = $project->status->nextStatus();

        $project->update([
            'status' => $nextStatus,
            'completed_at' => $nextStatus->isClosed() ? now() : $project->completed_at,
        ]);

        if ($nextStatus->isInVerification()) {
            $project->createVerificationReportIfNeeded();
        }

        event(new ProjectChanged($project, 'status changed'));
    }

    public function rollback(Project $project): void
    {
        $currentStatus = $project->status;

        if ($currentStatus->isReviewComplete()) {
            $project->getReviewReport()->rollbackReport();
        }

        $project->update([
            'status' => $currentStatus->previousStatus(),
            'completed_at' => $currentStatus->isClosed() ? null : $project->completed_at,
        ]);

        event(new ProjectChanged($project, 'status changed'));
    }

    public function completeReview(Project $project): void
    {
        $project->update(['status' => ProjectStatus::ReviewComplete]);

        event(new ProjectChanged($project, 'status changed'));
    }
}
