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
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[DataProvider('canCreateProjectProvider')]
    #[Test] public function can_create_project_authorization_cases(
        string $description,
        ?Roles $role,
        bool $isTeamMember,
        bool $hasPermission
    )
    {
        $user = User::factory()->create();
        $projectTeam = $this->makeTestTeam();
        if ($isTeamMember) {
            $team = $projectTeam;
        } else {
            $team = $this->makeTestTeam();
        }
        $team->users()->attach($user);
        if ($role) {
            $user->syncRoles([$role], $team);
        }

        $response = Livewire::actingAs($user)
            ->test(CreateProject::class, ['team' => $projectTeam])
            ->set('form.name', 'Test Project')
            ->set('form.site_url', 'https://testproject.com')
            ->set('form.description', $description)
            ->call('save');

        if ($hasPermission) {
            $response->assertRedirect(route('project.show', Project::latest()->first()));
        } else {
            $response->assertForbidden();
        }
    }

    public static function canCreateProjectProvider(): array
    {
        return [
            [
                'description' => 'Site admin, not a team member',
                'role' => Roles::SiteAdmin,
                'isTeamMember' => false,
                'hasPermission' => true,
            ],
            [
                'description' => 'Site admin, team member',
                'role' => Roles::SiteAdmin,
                'isTeamMember' => true,
                'hasPermission' => true,
            ],
            [
                'description' => 'Team admin, team member',
                'role' => Roles::TeamAdmin,
                'isTeamMember' => true,
                'hasPermission' => true,
            ],
            [
                'description' => 'Reviewer, team member',
                'role' => Roles::Reviewer,
                'isTeamMember' => true,
                'hasPermission' => true,
            ],
            [
                'description' => 'No role, team member',
                'role' => null,
                'isTeamMember' => true,
                'hasPermission' => false,
            ],
            [
                'description' => 'Team admin for another team',
                'role' => Roles::TeamAdmin,
                'isTeamMember' => false,
                'hasPermission' => false,
            ],
            [
                'description' => 'Reviewer for another team',
                'role' => Roles::Reviewer,
                'isTeamMember' => false,
                'hasPermission' => false,
            ],
        ];
    }
}
