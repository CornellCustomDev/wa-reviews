<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Livewire\Projects\UpdateVerifier;
use App\Livewire\Projects\Workflow;
use App\Models\Project;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class ProjectVerifierTest extends FeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeSiteimproveService();
    }

    #[Test]
    public function reviewer_can_assign_self_as_verifier()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);

        $updateVerifier = Livewire::test(UpdateVerifier::class, ['project' => $project]);

        // See that the user is in nonAssignedMembers initially
        $nonAssignedMembers = $updateVerifier->get('nonAssignedMembers');
        $this->assertContains($user->id, array_column($nonAssignedMembers, 'id'));

        // Update the project to assign the reviewer
        $updateVerifier->set('user', $user->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('close-update-verifier');

        // After assignment, user should not be in nonAssignedMembers
        $nonAssignedMembersAfter = $updateVerifier->get('nonAssignedMembers');
        $this->assertNotContains($user->id, array_column($nonAssignedMembersAfter, 'id'));
    }

    #[Test]
    public function reviewer_cannot_assign_others_as_verifier()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $user2 = $this->makeTestUser([Roles::Reviewer], $team);

        // User should not be able to assign project to someone else
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);
        Livewire::test(UpdateVerifier::class, ['project' => $project])
            ->set('user', $user2->id)
            ->call('save')
            ->assertForbidden()
            ->assertNotDispatched('close-update-verifier');

        // User should not be able to self-assign if project already has a verifier
        $project2 = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);
        $project2->assignVerifier($user2);
        Livewire::test(UpdateVerifier::class, ['project' => $project2])
            ->set('user', $user->id)
            ->call('save')
            ->assertForbidden()
            ->assertNotDispatched('close-update-verifier');
    }

    #[Test]
    public function verifier_can_update_project_status()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);

        // Cannot update status before being assigned as verifier
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'next')
            ->assertForbidden();

        $project->assignVerifier($user);

        // ReviewComplete → VerificationReview
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'next')
            ->assertDispatched('refresh-project');

        $this->assertEquals(ProjectStatus::VerificationReview, $project->fresh()->status);

        // VerificationReview → Closed
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'next')
            ->assertDispatched('refresh-project');

        $this->assertEquals(ProjectStatus::Closed, $project->fresh()->status);

        // Closed → VerificationReview
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'previous')
            ->assertDispatched('refresh-project');

        $this->assertEquals(ProjectStatus::VerificationReview, $project->fresh()->status);

        // VerificationReview → ReviewComplete
        Livewire::test(Workflow::class, ['project' => $project])
            ->call('updateStatus', 'previous')
            ->assertDispatched('refresh-project');

        $this->assertEquals(ProjectStatus::ReviewComplete, $project->fresh()->status);
    }

    #[Test]
    public function verifier_cannot_update_verifier_of_closed_project()
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::Closed,
        ]);

        Livewire::test(UpdateVerifier::class, ['project' => $project])
            ->set('user', $user->id)
            ->call('save')
            ->assertForbidden()
            ->assertNotDispatched('close-update-verifier');
    }
}
