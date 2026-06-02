<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Livewire\Projects\ViewProjects;
use App\Models\Project;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ViewProjectsTest extends FeatureTestCase
{
    #[Test] public function filters_active_projects_by_name(): void
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();

        Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'WCAG Review Alpha',
            'status' => ProjectStatus::NotStarted,
        ]);
        Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'Annual Audit Beta',
            'status' => ProjectStatus::NotStarted,
        ]);

        Livewire::test(ViewProjects::class)
            ->set('search', 'WCAG')
            ->assertSee('WCAG Review Alpha')
            ->assertDontSee('Annual Audit Beta');
    }

    #[Test] public function shows_no_results_when_search_has_no_match(): void
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();

        Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'WCAG Review Alpha',
            'status' => ProjectStatus::NotStarted,
        ]);

        Livewire::test(ViewProjects::class)
            ->set('search', 'xyz_nomatch_xyz')
            ->assertDontSee('WCAG Review Alpha');
    }
}
