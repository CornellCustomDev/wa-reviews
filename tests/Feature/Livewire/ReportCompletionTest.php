<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Livewire\Projects\Report;
use App\Models\Project;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ReportCompletionTest extends FeatureTestCase
{
    #[Test]
    public function complete_review_requires_summary(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
            'summary' => null,
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReview')
            ->assertHasErrors(['form.summary']);

        $this->assertEquals(ProjectStatus::InProgress, $project->fresh()->status);
    }

    #[Test]
    public function complete_review_advances_status_when_summary_is_filled(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
            'summary' => null,
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->set('form.summary', 'Overall the site has several critical issues.')
            ->call('completeReview')
            ->assertHasNoErrors()
            ->assertRedirect(route('project.show', $project));

        $this->assertEquals(ProjectStatus::ReviewComplete, $project->fresh()->status);
    }

    #[Test]
    public function complete_review_saves_all_report_fields(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->set('form.urls_included', 'https://example.com/page1')
            ->set('form.urls_excluded', 'https://example.com/admin')
            ->set('form.review_procedure', 'Tested with NVDA on Chrome.')
            ->set('form.summary', 'Several issues found.')
            ->call('completeReview')
            ->assertHasNoErrors();

        $project->refresh();
        $this->assertEquals('https://example.com/page1', $project->urls_included);
        $this->assertEquals('https://example.com/admin', $project->urls_excluded);
        $this->assertEquals('Tested with NVDA on Chrome.', $project->review_procedure);
        $this->assertEquals('Several issues found.', $project->summary);
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
        $project->assignToUser($otherUser);

        Livewire::test(Report::class, ['project' => $project])
            ->set('form.summary', 'Summary text.')
            ->call('completeReview')
            ->assertForbidden();
    }

    #[Test]
    public function report_fields_are_editable_on_report_page(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
            'urls_included' => 'Old URLs',
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->set('form.urls_included', 'New URLs')
            ->call('saveReport')
            ->assertHasNoErrors();

        $this->assertEquals('New URLs', $project->fresh()->urls_included);
    }

    #[Test]
    public function save_report_is_forbidden_for_non_reviewer(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $otherUser = $this->makeTestUser([Roles::Reviewer], $team);
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::InProgress,
        ]);
        $project->assignToUser($otherUser);

        Livewire::test(Report::class, ['project' => $project])
            ->set('form.urls_included', 'Hacked content')
            ->call('saveReport')
            ->assertForbidden();
    }
}
