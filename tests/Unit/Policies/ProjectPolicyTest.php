<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Project;
use App\Models\ProjectAssignment;
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
        $this->setupTeam($projectTeam, $user, $isTeamMember, $role);

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

    #[DataProvider('updateProjectProvider')]
    #[Test] public function update_project($role, $isTeamMember, $isReviewer, $status, $hasPermission, $description)
    {
        $user = User::factory()->create();
        $projectTeam = Team::factory()->create();
        $this->setupTeam($projectTeam, $user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, $isReviewer, $status);

        $result = (new ProjectPolicy())->update($user, $project);

        $this->assertEquals($hasPermission, $result, $description);
    }

    public static function updateProjectProvider(): array
    {
        // role, isTeamMember, isReviewer, status, hasPermission, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::Completed, false, 'SiteAdmin cannot update completed project'],
            [Roles::TeamAdmin, true, true, ProjectStatus::Completed, false, 'Team admin cannot update completed project'],
            [Roles::Reviewer, true, true, ProjectStatus::Completed, false, 'Reviewer cannot update completed project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::InProgress, true, 'Site admin can update in-progress project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::InProgress, true, 'Team admin can update in-progress project'],
            [Roles::Reviewer, true, true, ProjectStatus::InProgress, true, 'Reviewer can update in-progress project'],
            [Roles::Reviewer, true, false, ProjectStatus::InProgress, false, 'Team member cannot update in-progress project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can update not-started project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can update not-started project'],
            // TODO: Add permission for reviewers to update not-started projects
            [Roles::Reviewer, true, true, ProjectStatus::NotStarted, false, 'Reviewer cannot update not-started project'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, false, 'Team member cannot update not-started project'],

            [Roles::TeamAdmin, false, false, ProjectStatus::InProgress, false, 'Other team admin cannot update in-progress project'],
//            [Roles::Reviewer, false, true, ProjectStatus::InProgress, false, 'Other team reviewer cannot update in-progress project'],
        ];
    }

    private function setupTeam(Team $projectTeam, User $user, $isTeamMember, ?Roles $role): void
    {
        if ($isTeamMember) {
            $team = $projectTeam;
        } else {
            $team = Team::factory()->create();
        }
        $team->users()->attach($user);
        if ($role) {
            $user->addRole($role, $team);
        }
    }

    private function setupProject(Team $projectTeam, ?User $user = null, bool $isReviewer = false, ?ProjectStatus $status = null): Project
    {
        $project = Project::factory()->create([
            'team_id' => $projectTeam->id,
            'status' => $status ?? ProjectStatus::NotStarted,
        ]);
        if ($isReviewer) {
            $project->assignment()->create([
                'user_id' => $user->id,
            ]);
        }

        return $project;
    }
}
