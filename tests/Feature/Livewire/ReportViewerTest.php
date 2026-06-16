<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Livewire\Reports\ShowReport;
use App\Models\Project;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ReportViewerTest extends FeatureTestCase
{

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

        Livewire::test(ShowReport::class, ['report' => $project->reviewReport])
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

        Livewire::test(ShowReport::class, ['report' => $project->reviewReport])
            ->assertSee('Report Viewers');
    }

     #[Test]
     public function report_viewers_section_is_visible_when_closed(): void
     {
         $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
         $team = $user->teams()->first();
         $project = Project::factory()->create([
             'team_id' => $team->id,
             'status' => ProjectStatus::Closed,
         ]);
         $project->assignToUser($user);

         Livewire::test(ShowReport::class, ['report' => $project->reviewReport])
             ->assertSee('Report Viewers');
     }
}
