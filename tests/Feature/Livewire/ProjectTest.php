<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\ShowProject;
use App\Livewire\Projects\UpdateProject;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Enums\Roles;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ProjectTest extends FeatureTestCase
{
    protected Team $team;
    protected User $user;
    protected $start;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
    }

    #[Test] public function renders_successfully()
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();

        Livewire::test(CreateProject::class, ['team' => $team])
            ->assertStatus(200);
    }

    #[Test] public function can_create_project()
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();

        Livewire::test(CreateProject::class, ['team' => $team])
            ->set('form.name', 'Test Project')
            ->set('form.site_url', 'https://testproject.com')
            ->set('form.description', 'This is a test project')
            ->call('save')
            ->assertRedirect(route('project.show', Project::latest()->first()));
    }

    #[Test] public function can_show_project()
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();

        $project = Project::factory()->create(['team_id' => $team->id]);

        Livewire::test(ShowProject::class, ['project' => $project])
            ->assertStatus(200)
            ->assertSee($project->name);
    }

    #[Test] public function can_update_project()
    {
        $user = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $team = $user->teams()->first();

        $project = Project::factory()->create(['team_id' => $team->id]);

        Livewire::test(UpdateProject::class, ['project' => $project])
            ->set('form.name', 'Updated Project')
            ->set('form.site_url', 'https://updatedproject.com')
            ->set('form.description', 'This is an updated project')
            ->call('save')
            ->assertRedirect(route('project.show', $project));
    }

    #[Test] public function siteadmin_can_create_project()
    {
        $user = $this->getLoggedInTestUser([Roles::SiteAdmin]);
        $team = Team::factory()->create();

        // Confirm that the project.create route is accessible to site admins
        $this->actingAs($user)
            ->get(route('teams.project.create', $team))
            ->assertStatus(200);

        Livewire::test(CreateProject::class, ['team' => $team])
            ->set('form.name', 'Site Admin Project')
            ->set('form.site_url', 'https://siteadminproject.com')
            ->set('form.description', 'This is a project created by a site admin')
            ->call('save')
            ->assertRedirect(route('project.show', Project::latest()->first()));
    }
}
