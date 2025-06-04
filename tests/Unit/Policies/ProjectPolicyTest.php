<?php

namespace Tests\Unit\Policies;

use App\Enums\Roles;
use App\Models\Team;
use App\Models\User;
use App\Policies\ProjectPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\UsesTestDatabase;

class ProjectPolicyTest extends TestCase
{
    use UsesTestDatabase;

    #[DataProvider('createProjectProvider')]
    #[Test] public function create_project($role, $isTeamMember, $hasPermission, $description)
    {
        $user = User::factory()->create();
        $projectTeam = Team::factory()->create();
        if ($isTeamMember) {
            $team = $projectTeam;
        } else {
            $team = Team::factory()->create();
        }
        $team->users()->attach($user);
        if ($role) {
            $user->addRole($role, $team);
        }

        $result = (new ProjectPolicy())->create($user, $projectTeam);

        $this->assertEquals($hasPermission, $result, $description);
    }

    public static function createProjectProvider(): array
    {
        // role, isTeamMember, hasPermission, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin, not a team member'],
            [Roles::SiteAdmin, true, true, 'Site admin, team member'],
            [Roles::TeamAdmin, true, true, 'Team admin, team member'],
            [Roles::Reviewer, true, true, 'Reviewer, team member'],
            [null, true, false, 'No role, team member'],
            [Roles::TeamAdmin, false, false, 'Team admin for another team'],
            [Roles::Reviewer, false, false, 'Reviewer for another team'],
        ];
    }
}
