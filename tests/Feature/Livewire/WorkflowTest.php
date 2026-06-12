<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\ReportType;
use App\Enums\Roles;
use App\Livewire\Projects\Report;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\Workflow;
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

    #[Test]
    public function shows_start_review_button_when_reviewer_assigned_and_not_started(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::NotStarted,
        ]);
        $project->assignToUser($user);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('Start Review')
            ->assertDontSee('Review &amp; Finalize Report');
    }

    #[Test]
    public function shows_review_and_finalize_button_when_in_progress(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($user);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('Review & Finalize Report')
            ->assertDontSee('Start Review');
    }

    #[Test]
    public function shows_view_report_link_when_closed(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::Closed,
        ]);
        $project->assignToUser($user);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report');
    }

    #[Test]
    public function report_viewers_section_is_hidden_when_not_started(): void
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::NotStarted,
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->assertDontSee('Report Viewers');
    }

    #[Test]
    public function report_viewers_section_is_visible_when_in_progress(): void
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->assertSee('Report Viewers');
    }

    #[Test]
    public function report_tab_is_not_shown_on_project_page(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);

        Livewire::test(ShowProject::class, ['project' => $project])
            ->assertDontSeeHtml('name="report"');
    }

    #[Test]
    public function view_report_button_label_is_view_verification_report_when_in_verification_review(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::VerificationReview,
        ]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Verification Report')
            ->assertDontSee('View Report');
    }

    #[Test]
    public function view_report_button_label_is_view_report_when_in_review_complete(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertDontSee('View Verification Report');
    }

    #[Test]
    public function closed_cta_shows_view_verification_report_when_verification_report_exists(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::Closed,
        ]);
        $project->assignToUser($user);
        $project->reports()->create(['type' => ReportType::Verification]);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Verification Report');
    }

    #[Test]
    public function closed_cta_shows_view_report_when_no_verification_report_exists(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::Closed,
        ]);
        $project->assignToUser($user);

        Livewire::test(Workflow::class, ['project' => $project])
            ->assertSee('View Report')
            ->assertDontSee('View Verification Report');
    }
}
