<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Scopes\AddScope;
use App\Livewire\Scopes\CreateScope;
use App\Livewire\Scopes\ShowScope;
use App\Livewire\Scopes\UpdateScope;
use App\Models\Project;
use App\Models\Scope;
use App\Models\Team;
use App\Models\User;
use App\Enums\Roles;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ScopeTest extends FeatureTestCase
{
    protected Team $team;
    protected User $user;
    protected Project $project;
    protected $start;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
    }

    #[Test] public function renders_successfully()
    {
        $user = $this->getLoggedInTestUser();
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);

        Livewire::test(CreateScope::class, ['project' => $project])
            ->assertStatus(200);
    }

    #[Test] public function can_create_scope()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        // assign the user as a reviewer of the project
        $project->assignToUser($user);

        Livewire::test(CreateScope::class, ['project' => $project])
            ->set('form.title', 'Test Scope')
            ->set('form.url', 'https://testscope.com')
            ->set('form.notes', 'This is a test scope')
            ->call('save')
            ->assertRedirect(route('scope.show', Scope::latest()->first()));
    }

    #[Test] public function can_add_scope()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        // assign the user as a reviewer of the project
        $project->assignToUser($user);

        Livewire::test(AddScope::class, ['project' => $project])
            ->set('form.title', 'Test Scope')
            ->set('form.url', 'https://testscope.com')
            ->set('form.notes', 'This is a test scope')
            ->call('save')
            ->assertDispatched('refresh-scopes');
    }

    #[Test] public function can_show_scope()
    {
        $user = $this->getLoggedInTestUser();
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
            'title' => 'Test Scope',
            'url' => 'https://testscope.com'
        ]);

        Livewire::test(ShowScope::class, ['scope' => $scope])
            ->assertStatus(200)
            ->assertSee($scope->title);
    }

    #[Test] public function reviewer_can_update_scope()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $project->assignToUser($user);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
            'title' => 'Test Scope',
            'url' => 'https://testscope.com'
        ]);

        Livewire::test(UpdateScope::class, ['scope' => $scope])
            ->set('form.title', 'Updated Scope')
            ->set('form.url', 'https://updatedscope.com')
            ->set('form.notes', 'This is an updated scope')
            ->call('save')
            ->assertRedirect(route('scope.show', $scope));

        $this->assertDatabaseHas('scopes', [
            'id' => $scope->id,
            'title' => 'Updated Scope',
            'url' => 'https://updatedscope.com',
            'notes' => 'This is an updated scope',
        ]);
    }

    #[Test] public function member_cannot_update_scope()
    {
        $user = $this->getLoggedInTestUser();
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
            'title' => 'Test Scope',
            'url' => 'https://testscope.com'
        ]);

        Livewire::test(UpdateScope::class, ['scope' => $scope])
            ->set('form.title', 'Updated Scope')
            ->set('form.url', 'https://updatedscope.com')
            ->set('form.notes', 'This is an updated scope')
            ->call('save')
            ->assertForbidden();
    }
}
