<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\ReportType;
use App\Enums\Roles;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\Workflow;
use App\Livewire\Reports\ShowReport;
use App\Models\Project;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class WorkflowTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
    }

    private function setupReviewProject(ProjectStatus $status): Project
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => $status,
        ]);
        $project->assignToUser($user);

        if (! $status->isActive()) {
            $project->reviewReport()->update(['completed_at' => now()]);
        }

        return $project;
    }

    #[Test]
    public function shows_start_review_button_when_reviewer_assigned_and_not_started(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::NotStarted);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('Start Review')
            ->assertDontSee('Review &amp; Finalize Report');
    }

    #[Test]
    public function start_review_advances_to_in_progress(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::NotStarted);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('Start Review')
            ->assertDontSee('Review & Finalize Report')
            ->call('startReview')
            ->assertSee('Review & Finalize Report')
            ->assertDontSee('Start Review');

        $project->refresh();

        $this->assertTrue($project->isInProgress());
    }

    #[Test]
    public function shows_review_and_finalize_button_when_in_progress(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::InProgress);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('Review & Finalize Report')
            ->assertDontSee('Start Review');
    }

    #[Test]
    public function shows_view_report_link_when_closed(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::Closed);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report');
    }

    #[Test]
    public function assign_verifier_button_is_visible_when_review_complete(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::ReviewComplete);
        $project->reports()->create(['type' => ReportType::Verification]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertSee('Assign Verifier');
    }

    #[Test]
    public function start_verification_report_button_is_visible_when_assigned(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::ReviewComplete);
        $project->reports()->create(['type' => ReportType::Verification]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->call('assignCurrentUserAsVerifier');

        $project->refresh();
        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertSee('Start Verification')
            ->assertDontSee('Review Verification Report');
    }

    #[Test]
    public function start_verification_advances_to_verification_review(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::ReviewComplete);
        $project->reports()->create(['type' => ReportType::Verification]);
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('assignCurrentUserAsVerifier');

        $project->refresh();
        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertSee('Start Verification')
            ->call('startVerification')
            ->assertSee('Review Verification Report')
            ->assertDontSee('Start Verification');
    }

    #[Test]
    public function review_verification_report_button_is_visible_in_verification_review(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::VerificationReview);
        $project->reports()->create(['type' => ReportType::Verification]);

        $this->assertFalse($project->hasBeenReviewed() && $project->verifier);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertSee('Review Verification Report')
            ->assertDontSee('Start Verification');
    }

    #[Test]
    public function close_project_advances_to_closed(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::VerificationReview);
        $project->reports()->create([
            'type' => ReportType::Verification,
            'completed_at' => now(),
        ]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertSee('Review Verification Report')
            ->call('closeProject')
            ->assertSee('View Report')
            ->assertSee('View Verification Report')
            ->assertDontSee('Review Verification Report');

        $project->refresh();
        $this->assertTrue($project->isClosed());
    }

    #[Test]
    public function closed_cta_shows_view_verification_report_when_verification_report_exists(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::Closed);
        $project->reports()->create([
            'type' => ReportType::Verification,
            'completed_at' => now(),
        ]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertSee('View Verification Report');
    }

    #[Test]
    public function closed_cta_shows_view_report_when_no_verification_report_exists(): void
    {
        $project = $this->setupReviewProject(ProjectStatus::Closed);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertDontSee('View Verification Report');
    }
}
