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
            'summary' => 'Reviewed',
        ]);
        $project->assignToUser($user);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReview')
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
            'summary' => 'Reviewed',
        ]);
        $project->assignToUser($otherUser);

        Livewire::test(Report::class, ['project' => $project])
            ->call('completeReview')
            ->assertForbidden();
    }
}
