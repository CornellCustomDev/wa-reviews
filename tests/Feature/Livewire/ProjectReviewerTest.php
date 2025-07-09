<?php

namespace Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Livewire\Projects\CreateProject;
use App\Livewire\Projects\UpdateReviewer;
use App\Livewire\Projects\Workflow;
use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Enums\Roles;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ProjectReviewerTest extends FeatureTestCase
{
    protected Team $team;
    protected User $user;
    protected $start;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
    }

    #[Test] public function reviewer_can_create_project()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();

        Livewire::test(CreateProject::class, ['team' => $team])
            ->set('form.name', 'Reviewer Project')
            ->set('form.site_url', 'https://reviewerproject.com')
            ->set('form.description', 'This is a project created by a reviewer')
            ->call('save')
            ->assertRedirect(route('project.show', Project::latest()->first()));
    }

    #[Test] public function reviewer_can_assign_self_as_reviewer()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);

        $updateReviewer = Livewire::test(UpdateReviewer::class, ['project' => $project]);

        // See that the user is in nonAssignedMembers initially
        $nonAssignedMembers = $updateReviewer->get('nonAssignedMembers');
        $this->assertContains($user->id, array_column($nonAssignedMembers, 'id'));

        // Update the project to assign the reviewer
        $updateReviewer->set('user', $user->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-update-reviewer');

        // After assignment, user should not be in nonAssignedMembers
        $nonAssignedMembersAfter = $updateReviewer->get('nonAssignedMembers');
        $this->assertNotContains($user->id, array_column($nonAssignedMembersAfter, 'id'));
    }

    #[Test] public function reviewer_cannot_assign_others()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $user2 = $this->makeTestUser([Roles::Reviewer], $team);

        // User should not be able to assign project to someone else
        $project = Project::factory()->create(['team_id' => $team->id]);
        Livewire::test(UpdateReviewer::class, ['project' => $project])
            ->set('user', $user2->id)
            ->call('save')
            ->assertForbidden()
            ->assertNotDispatched('close-update-reviewer');

        // User should not be able to assign self to project2, since it is already assigned
        $project2 = Project::factory()->create(['team_id' => $team->id]);
        $project2->assignToUser($user2);
        Livewire::test(UpdateReviewer::class, ['project' => $project2])
            ->set('user', $user->id)
            ->call('save')
            ->assertForbidden()
            ->assertNotDispatched('close-update-reviewer');
    }

    #[Test] public function reviewer_can_update_project_status()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);

        // Cannot update if not assigned as reviewer
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'next')
            ->assertForbidden();

        // Assign the user as reviewer
        $project->assignToUser($user);

        // Now the reviewer can update the status
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'next')
            ->assertDispatched('refresh-project');

        $this->assertEquals(ProjectStatus::InProgress, $project->fresh()->status);

        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'previous')
            ->assertDispatched('refresh-project');

        $this->assertEquals(ProjectStatus::NotStarted, $project->fresh()->status);
    }

    #[Test] public function reviewer_cannot_update_reviewer_of_completed_project()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id, 'status' => ProjectStatus::Completed]);

        // Attempt assign the project to the user
        Livewire::test(UpdateReviewer::class, ['project' => $project])
            ->set('user', $user->id)
            ->call('save')
            ->assertForbidden()
            ->assertNotDispatched('close-update-reviewer');
    }
}
