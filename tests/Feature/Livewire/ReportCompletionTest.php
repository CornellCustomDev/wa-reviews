<?php

namespace Tests\Feature\Livewire;

use App\Enums\IssueStatus;
use App\Enums\ProjectStatus;
use App\Enums\ReportType;
use App\Enums\Roles;
use App\Livewire\Projects\Report;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Scope;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ReportCompletionTest extends FeatureTestCase
{
    private function makeIssueForProject(Project $project, array $attributes = []): Issue
    {
        $scope = Scope::factory()->create(['project_id' => $project->id]);

        return Issue::factory()->create(array_merge([
            'project_id' => $project->id,
            'scope_id' => $scope->id,
        ], $attributes));
    }


    #[Test]
    public function creating_a_project_automatically_creates_a_review_report(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create(['team_id' => $user->teams()->first()->id]);

        $report = $project->reports()->where('type', 'review')->first();

        $this->assertNotNull($report, 'A review report should be created when a project is created.');
    }

    #[Test]
    public function is_report_ready_returns_false_when_review_report_has_no_summary(): void
    {
        $project = Project::factory()->create(['status' => ProjectStatus::InProgress]);

        $this->assertFalse($project->getReviewReport()->isReady());
    }

    #[Test]
    public function is_report_ready_returns_true_when_in_progress_and_report_has_summary(): void
    {
        $project = Project::factory()->create(['status' => ProjectStatus::InProgress]);
        $report = $project->getReviewReport();
        $report->update(['summary' => 'Some findings']);

        $this->assertTrue($report->isReady());
    }

    #[Test]
    public function complete_review_requires_summary(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport')
            ->assertForbidden();

        $this->assertEquals(ProjectStatus::InProgress, $project->fresh()->status);
    }

    #[Test]
    public function complete_review_advances_status(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->getReviewReport()->update(['summary' => 'Reviewed']);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport')
            ->assertHasNoErrors()
            ->assertRedirect(route('project.show', $project));

        $this->assertEquals(ProjectStatus::ReviewComplete, $project->fresh()->status);
    }

    #[Test]
    public function complete_review_is_forbidden_for_non_reviewer(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $otherUser = $this->makeTestUser([Roles::Reviewer], $team);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->getReviewReport()->update(['summary' => 'Reviewed']);
        $project->assignToUser($otherUser);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport')
            ->assertForbidden();
    }

    #[Test]
    public function issues_with_null_status_are_set_to_reviewed_on_review_completion(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->getReviewReport()->update(['summary' => 'Test summary']);
        $project->assignToUser($user);
        $issue = $this->makeIssueForProject($project, ['status' => null]);
        $project->refresh();

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport');

        $report = $project->refresh()->getReviewReport();

        $this->assertSame(IssueStatus::Reviewed, $issue->fresh()->status);
        $this->assertSame(IssueStatus::Reviewed, $report->issues()->first()->status);
    }

    #[Test]
    public function issues_with_existing_status_are_not_changed_on_review_completion(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($user);
        $issue = $this->makeIssueForProject($project, ['status' => IssueStatus::WontFix]);
        $project->getReviewReport()->update(['summary' => 'Test summary']);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport');

        $this->assertSame(IssueStatus::WontFix, $issue->fresh()->status);

        $report = $project->refresh()->getReviewReport();
        $this->assertCount(1, $report->issues);
    }

    #[Test]
    public function issues_without_report_id_are_assigned_the_review_report_id_on_completion(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->getReviewReport()->update(['summary' => 'Test summary']);
        $project->assignToUser($user);
        $issue = $this->makeIssueForProject($project);
        $this->assertNull($issue->report_id);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport');

        $report = $project->refresh()->getReviewReport();
        $this->assertNotNull($report, 'A report record should exist on review completion.');
        $this->assertSame($report->id, $issue->fresh()->report_id);
    }

    #[Test]
    public function report_issues_pivot_is_populated_for_all_project_issues_on_review_completion(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->getReviewReport()->update(['summary' => 'Test summary']);
        $project->assignToUser($user);
        $scope = Scope::factory()->create(['project_id' => $project->id]);
        $issues = Issue::factory()->count(3)->create([
            'project_id' => $project->id,
            'scope_id' => $scope->id,
        ]);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReport');

        $report = $project->refresh()->getReviewReport();
        $this->assertNotNull($report, 'A report record should exist on review completion.');

        $this->assertCount(3, $report->issues, 'All project issues should be associated with the review report.');
    }

    #[Test]
    public function report_page_shows_verification_report_when_project_is_in_verification_review(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::VerificationReview,
        ]);
        $project->assignToUser($user);
        $project->reports()->create(['type' => ReportType::Verification]);

        $verificationReport = $project->getVerificationReport();

        $component = Livewire::test(Report::class, ['project' => $project]);
        $this->assertEquals($verificationReport->id, $component->instance()->report->id);
    }

    #[Test]
    public function report_page_shows_review_report_when_project_is_in_progress(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($user);

        $reviewReport = $project->getReviewReport();

        $component = Livewire::test(Report::class, ['project' => $project]);
        $this->assertEquals($reviewReport->id, $component->instance()->report->id);
    }

    #[Test]
    public function report_page_falls_back_to_review_report_when_closed_with_no_verification_report(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $project = Project::factory()->create([
            'team_id' => $user->teams()->first()->id,
            'status' => ProjectStatus::Closed,
        ]);
        $project->assignToUser($user);

        $reviewReport = $project->getReviewReport();

        $component = Livewire::test(Report::class, ['project' => $project]);
        $this->assertEquals($reviewReport->id, $component->instance()->report->id);
    }
}
