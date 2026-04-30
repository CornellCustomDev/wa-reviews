<?php

namespace Tests\Unit\Models;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\ProjectAssignment;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class ProjectTest extends TestCase
{
    use TestDatabase;

    #[Test]
    public function verifier_relationship_is_null_when_no_verifier_is_assigned()
    {
        $user = User::factory()->create();
        $team = self::setupTeam($user, isTeamMember: true, role: Roles::Reviewer);
        $verifier = User::factory()->create();

        // Has a reviewer assignment (role='reviewer') but no active verifier assignment
        $project = self::setupProject($team, $user, isReviewer: true, status: ProjectStatus::ReviewComplete);

        // Create and soft-delete a verifier assignment, leaving only a deleted row
        $assignment = $project->verifierAssignment()->create(['user_id' => $verifier->id, 'role' => ProjectAssignment::VERIFIER]);
        $assignment->delete();
        $project->refresh();

        $this->assertNull($project->verifierAssignment);
        $this->assertNull($project->verifier);
    }
}
