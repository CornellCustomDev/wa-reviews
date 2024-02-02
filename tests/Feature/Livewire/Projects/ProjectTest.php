<?php

namespace Tests\Feature\Livewire\Projects;

use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\UpdateProject;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\Feature\FeatureTestCase;

class ProjectTest extends FeatureTestCase
{
    /** @test */
    public function renders_successfully()
    {
        Livewire::test(CreateProject::class)
            ->assertStatus(200);
    }

    /** @test */
    public function can_create_project()
    {
        Livewire::test(CreateProject::class)
            ->set('form.name', 'Test Project')
            ->set('form.site_url', 'https://testproject.com')
            ->set('form.description', 'This is a test project')
            ->call('save')
            ->assertRedirect(route('projects.index'));
    }

    /** @test */
    public function can_update_project()
    {
        $project = Project::factory()->create();

        Livewire::test(UpdateProject::class, ['project' => $project])
            ->set('form.name', 'Updated Project')
            ->set('form.site_url', 'https://updatedproject.com')
            ->set('form.description', 'This is an updated project')
            ->call('save')
            ->assertRedirect(route('projects.index'));
    }


}
