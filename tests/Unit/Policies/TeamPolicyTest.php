<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\User;
use App\Policies\TeamPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class TeamPolicyTest extends TestCase
{
    use TestDatabase;

    #[DataProvider('viewAnyTeamProvider')]
    #[Test] public function viewAny_team($role, $hasTeam, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $hasTeam, $role);

        $result = (new TeamPolicy())->viewAny($user);
        $this->assertEquals($expected, $result, $description);
    }

    public static function viewAnyTeamProvider(): array
    {
        // role, hasTeam, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can view teams'],
            [null, true, true, 'User with a team can view teams'],
            [null, null, false, 'User without a team cannot view teams'],
        ];
    }

    #[DataProvider('viewTeamProvider')]
    #[Test] public function view_team($role, $isTeamMember, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);

        $result = (new TeamPolicy())->view($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function viewTeamProvider(): array
    {
        // role, isTeamMember, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can view team'],
            [Roles::TeamAdmin, true, true, 'Team admin can view their team'],
            [null, true, true, 'Team member can view team'],
            [Roles::TeamAdmin, null, false, 'Non-member, cannot view team'],
        ];
    }

    #[DataProvider('manageTeamProvider')]
    #[Test] public function manage_team($role, $isTeamMember, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);

        $result = (new TeamPolicy())->manageTeam($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function manageTeamProvider(): array
    {
        // role, isTeamMember, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can manage team'],
            [Roles::TeamAdmin, true, true, 'Team admin can manage their team'],
            [null, true, false, 'Team member cannot manage team'],
            [Roles::TeamAdmin, false, false, 'Other team admin cannot manage team'],
        ];
    }

    #[DataProvider('createTeamProvider')]
    #[Test] public function create_team($role, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, true, $role);

        $result = (new TeamPolicy())->create($user);
        $this->assertEquals($expected, $result, $description);
    }

    public static function createTeamProvider(): array
    {
        // role, expected, description
        return [
            [Roles::SiteAdmin, true, 'Site admin can create team'],
            [Roles::TeamAdmin, false, 'Team admin cannot create team'],
        ];
    }

    #[DataProvider('updateTeamProvider')]
    #[Test] public function update_team($role, $isTeamMember, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);
        $result = (new TeamPolicy())->update($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function updateTeamProvider(): array
    {
        // role, isTeamMember, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can update team'],
            [Roles::TeamAdmin, true, true, 'Team admin can update their team'],
            [null, true, false, 'Team member cannot update team'],
            [Roles::TeamAdmin, false, false, 'Other team admin cannot update team'],
        ];
    }

    #[DataProvider('deleteTeamProvider')]
    #[Test] public function delete_team($role, $isTeamMember, $hasProjects, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);
        if ($hasProjects) {
            $this->setupProject($team, $user, status: ProjectStatus::NotStarted);
        }
        $result = (new TeamPolicy())->delete($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function deleteTeamProvider(): array
    {
        // role, isTeamMember, hasProjects, expected, description
        return [
            [Roles::SiteAdmin, false, false, true, 'Site admin can delete team with no projects'],
            [Roles::SiteAdmin, false, true, false, 'Site admin cannot delete team with projects'],
            [Roles::TeamAdmin, true, false, false, 'Team admin cannot delete team'],
            [null, true, false, false, 'Team member cannot delete team'],
        ];
    }

    #[DataProvider('manageProjectsProvider')]
    #[Test] public function manageProjects_team($role, $isTeamMember, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);
        $result = (new TeamPolicy())->manageProjects($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function manageProjectsProvider(): array
    {
        // role, isTeamMember, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can manage projects'],
            [Roles::TeamAdmin, true, true, 'Team admin can manage projects'],
            [null, true, false, 'Team member cannot manage projects'],
            [Roles::TeamAdmin, false, false, 'Other team admin cannot manage projects'],
        ];
    }

    #[DataProvider('createProjectsProvider')]
    #[Test] public function createProjects_team($role, $isTeamMember, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);
        $result = (new TeamPolicy())->createProjects($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function createProjectsProvider(): array
    {
        // role, isTeamMember, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can create projects'],
            [Roles::TeamAdmin, true, true, 'Team admin can create projects'],
            [null, true, false, 'Team member cannot create projects'],
            [Roles::TeamAdmin, false, false, 'Other team admin cannot create projects'],
        ];
    }

    #[DataProvider('editProjectsProvider')]
    #[Test] public function editProjects_team($role, $isTeamMember, $expected, $description)
    {
        $user = User::factory()->create();
        $team = $this->setupTeam($user, $isTeamMember, $role);
        $result = (new TeamPolicy())->editProjects($user, $team);
        $this->assertEquals($expected, $result, $description);
    }

    public static function editProjectsProvider(): array
    {
        // role, isTeamMember, expected, description
        return [
            [Roles::SiteAdmin, false, true, 'Site admin can edit projects'],
            [Roles::TeamAdmin, true, true, 'Team admin can edit projects'],
            [null, true, false, 'Team member cannot edit projects'],
            [Roles::TeamAdmin, false, false, 'Other team admin cannot edit projects'],
        ];
    }
}

