<?php

namespace Tests\Feature\Services;

use App\Enums\ProjectStatus;
use App\Enums\ReportType;
use App\Enums\Roles;
use App\Models\Project;
use App\Services\ProjectWorkflowService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ProjectWorkflowServiceTest extends FeatureTestCase
{
    private ProjectWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
        $this->service = new ProjectWorkflowService();
    }

    #[Test]
    public function advance_moves_project_to_next_status(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::NotStarted,
        ]);

        $this->service->advance($project);

        $this->assertEquals(ProjectStatus::InProgress, $project->fresh()->status);
    }

    #[Test]
    public function advance_sets_completed_at_when_transitioning_to_closed(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::VerificationReview,
        ]);

        $this->service->advance($project);

        $project->refresh();
        $this->assertEquals(ProjectStatus::Closed, $project->status);
        $this->assertNotNull($project->completed_at);
    }

    #[Test]
    public function advance_does_not_set_completed_at_for_non_closed_transitions(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::NotStarted,
            'completed_at' => null,
        ]);

        $this->service->advance($project);

        $this->assertNull($project->fresh()->completed_at);
    }

    #[Test]
    public function advance_creates_verification_report_when_transitioning_to_verification_review(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);
        $this->assertCount(1, $project->reports); // only the review report

        $this->service->advance($project);

        $project->refresh();
        $this->assertEquals(ProjectStatus::VerificationReview, $project->status);
        $this->assertCount(2, $project->reports);
        $this->assertNotNull($project->reports()->where('type', ReportType::Verification)->first());
    }

    #[Test]
    public function advance_does_not_duplicate_verification_report_on_re_entry(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);
        $this->service->advance($project); // → VerificationReview (creates verification report)
        $this->service->rollback($project); // → ReviewComplete
        $this->service->advance($project); // → VerificationReview again

        $project->refresh();
        $this->assertCount(
            1,
            $project->reports()->where('type', ReportType::Verification)->get()
        );
    }

    #[Test]
    public function advance_does_not_create_verification_report_for_other_transitions(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::NotStarted,
        ]);

        $this->service->advance($project);

        $this->assertCount(1, $project->fresh()->reports); // only the review report
    }

    #[Test]
    public function rollback_moves_project_to_previous_status(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);

        $this->service->rollback($project);

        $this->assertEquals(ProjectStatus::NotStarted, $project->fresh()->status);
    }

    #[Test]
    public function rollback_clears_completed_at_when_rolling_back_from_closed(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::Closed,
            'completed_at' => now(),
        ]);

        $this->service->rollback($project);

        $project->refresh();
        $this->assertEquals(ProjectStatus::VerificationReview, $project->status);
        $this->assertNull($project->completed_at);
    }

    #[Test]
    public function rollback_preserves_completed_at_for_non_closed_rollbacks(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $completedAt = now()->subDays(2);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::VerificationReview,
            'completed_at' => $completedAt,
        ]);

        $this->service->rollback($project);

        $this->assertEquals(
            $completedAt->toDateTimeString(),
            $project->fresh()->completed_at->toDateTimeString()
        );
    }

    #[Test]
    public function rollback_clears_review_report_when_rolling_back_from_review_complete(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);
        $report = $project->getReviewReport();
        $report->update([
            'completed_at' => now(),
            'completed_by' => $user->id,
            'summary' => 'Test summary',
        ]);

        $this->service->rollback($project);

        $this->assertNull($report->fresh()->completed_at);
        $this->assertNull($report->fresh()->completed_by);
    }

    #[Test]
    public function complete_review_sets_project_status_to_review_complete(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);

        $this->service->completeReview($project);

        $this->assertEquals(ProjectStatus::ReviewComplete, $project->fresh()->status);
    }

    #[Test]
    public function complete_review_does_not_set_project_completed_at(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
            'completed_at' => null,
        ]);

        $this->service->completeReview($project);

        $this->assertNull($project->fresh()->completed_at);
    }
}
