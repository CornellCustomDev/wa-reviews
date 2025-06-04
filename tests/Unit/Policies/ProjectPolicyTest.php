<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Project;
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

    #[DataProvider('viewProjectProvider')]
    #[Test] public function view_project($role, $isTeamMember, $isReportViewer, $status, $hasPermission, $description)
    {
        $user = User::factory()->create();
        $projectTeam = Team::factory()->create();
        $this->setupTeam($projectTeam, $user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReportViewer: $isReportViewer, status: $status);

        $result = (new ProjectPolicy())->view($user, $project);

        $this->assertEquals($hasPermission, $result, $description);
    }

    public static function viewProjectProvider(): array
    {
        // role, isTeamMember, isReportViewer, status, hasPermission, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::InProgress, true, 'Site admin can view in-progress project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::InProgress, true, 'Team admin can view in-progress project'],
            [null, true, false, ProjectStatus::InProgress, true, 'Team member can view in-progress project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can view non-started project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can view non-started project'],
            [null, true, false, ProjectStatus::NotStarted, true, 'Team member can view non-started project'],

            [Roles::TeamAdmin, false, false, ProjectStatus::Completed, false, 'Other team admin cannot view completed project'],
            [null, false, false, ProjectStatus::Completed, false, 'Other team member cannot view completed project'],

            [null, false, true, ProjectStatus::NotStarted, false, 'Report viewer cannot view not-started project'],
            [null, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot view in-progress project'],
            [null, false, true, ProjectStatus::Completed, true, 'Report viewer can view completed project'],
        ];
    }

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
        $project = $this->setupProject($projectTeam, $user, $isReviewer, status: $status);

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
            [Roles::Reviewer, true, true, ProjectStatus::NotStarted, true, 'Reviewer can update not-started project'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, false, 'Team member cannot update not-started project'],

            [Roles::TeamAdmin, false, false, ProjectStatus::InProgress, false, 'Other team admin cannot update in-progress project'],
            [Roles::Reviewer, false, true, ProjectStatus::InProgress, false, 'Other team reviewer cannot update in-progress project'],
        ];
    }

    #[DataProvider('updateReviewerProvider')]
    #[Test] public function update_reviewer($role, $isTeamMember, $isReviewer, $status, $hasPermission, $description)
    {
        $user = User::factory()->create();
        $projectTeam = Team::factory()->create();
        $this->setupTeam($projectTeam, $user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, status: $status);

        $result = (new ProjectPolicy())->updateReviewer($user, $project);

        $this->assertEquals($hasPermission, $result, $description);
    }

    public static function updateReviewerProvider(): array
    {
        // role, isTeamMember, isReviewer, status, hasPermission, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::Completed, false, 'SiteAdmin cannot update reviewer of completed project'],
            [Roles::TeamAdmin, true, true, ProjectStatus::Completed, false, 'Team admin cannot update reviewer of completed project'],
            [Roles::Reviewer, true, true, ProjectStatus::Completed, false, 'Reviewer cannot update reviewer of completed project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::InProgress, true, 'Site admin can update reviewer of in-progress project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::InProgress, true, 'Team admin can update reviewer of in-progress project'],
            [Roles::Reviewer, true, true, ProjectStatus::InProgress, true, 'Assigned reviewer can update reviewer of in-progress project'],
            [Roles::Reviewer, true, false, ProjectStatus::InProgress, false, 'Team member cannot update reviewer of in-progress project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can update reviewer of not-started project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can update reviewer of not-started project'],
            [Roles::Reviewer, true, true, ProjectStatus::NotStarted, true, 'Assigned reviewer can update reviewer of not-started project'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, true, 'Team member can update reviewer of not-started project'],

            [Roles::TeamAdmin, false, false, ProjectStatus::NotStarted, false, 'Other team admin cannot update reviewer of project'],
            [Roles::Reviewer, false, true, ProjectStatus::NotStarted, false, 'Other team member cannot update reviewer of project'],
        ];
    }

    #[DataProvider('updateStatusProvider')]
    #[Test] public function update_status($role, $isTeamMember, $isReviewer, $status, $hasPermission, $description)
    {
        $user = User::factory()->create();
        $projectTeam = Team::factory()->create();
        $this->setupTeam($projectTeam, $user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, status: $status);

        $result = (new ProjectPolicy())->updateStatus($user, $project);

        $this->assertEquals($hasPermission, $result, $description);
    }

    public static function updateStatusProvider(): array
    {
        // role, isTeamMember, isReviewer, status, hasPermission, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::Completed, true, 'SiteAdmin can update status of completed project'],
            [Roles::TeamAdmin, true, true, ProjectStatus::Completed, true, 'Team admin can update status of completed project'],
            [Roles::Reviewer, true, true, ProjectStatus::Completed, true, 'Reviewer can update status of completed project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::InProgress, true, 'Site admin can update status of in-progress project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::InProgress, true, 'Team admin can update status of in-progress project'],
            [Roles::Reviewer, true, true, ProjectStatus::InProgress, true, 'Reviewer can update status of in-progress project'],
            [Roles::Reviewer, true, false, ProjectStatus::InProgress, false, 'Team member cannot update status of in-progress project'],

            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can update status of not-started project'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can update status of not-started project'],
            [Roles::Reviewer, true, true, ProjectStatus::NotStarted, true, 'Reviewer can update status of not-started project'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, false , 'Team member cannot update status of not-started project'],

            [Roles::TeamAdmin, false, false, ProjectStatus::InProgress, false, 'Other team admin cannot update status of in-progress project'],
            [Roles::Reviewer, false, true, ProjectStatus::InProgress, false, 'Other team reviewer cannot update status of in-progress project'],
        ];
    }

    #[DataProvider('deleteProjectProvider')]
    #[Test] public function delete_project($role, $isTeamMember, $status, $hasPermission, $description)
    {
        $user = User::factory()->create();
        $projectTeam = Team::factory()->create();
        $this->setupTeam($projectTeam, $user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, status: $status);

        $result = (new ProjectPolicy())->delete($user, $project);

        $this->assertEquals($hasPermission, $result, $description);
    }

    public static function deleteProjectProvider(): array
    {
        // role, isTeamMember, status, hasPermission, description
        return [
            [Roles::SiteAdmin, false, ProjectStatus::Completed, true, 'Site admin can delete completed project'],
            [Roles::TeamAdmin, true, ProjectStatus::Completed, false, 'Team admin cannot delete completed project'],
            [null, true, ProjectStatus::Completed, false, 'Team member cannot delete completed project'],

            [Roles::SiteAdmin, false, ProjectStatus::InProgress, true, 'Site admin can delete in-progress project'],
            [Roles::TeamAdmin, true, ProjectStatus::InProgress, true, 'Team admin can delete in-progress project'],
            [null, true, ProjectStatus::InProgress, false, 'Team member cannot delete in-progress project'],

            [Roles::SiteAdmin, false, ProjectStatus::NotStarted, true, 'Site admin can delete not-started project'],
            [Roles::TeamAdmin, true, ProjectStatus::NotStarted, true, 'Team admin can delete not-started project'],
            [null, true, ProjectStatus::NotStarted, false , 'Team member cannot delete not-started project'],

            [Roles::TeamAdmin, false, ProjectStatus::Completed , false , 'Other team admin cannot delete completed project'],
            [null , false , ProjectStatus::Completed , false , 'Other team member cannot delete completed project'],
        ];
    }

    public static function setupTeam(Team $projectTeam, User $user, $isTeamMember, ?Roles $role): void
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

    public static function setupProject(
        Team $projectTeam,
        ?User $user = null,
        bool $isReviewer = false,
        bool $isReportViewer = false,
        ?ProjectStatus $status = null
    ): Project
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
        if ($isReportViewer) {
            $project->reportViewers()->attach($user->id);
        }

        return $project;
    }
}
