<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Scope;
use App\Models\User;
use App\Policies\ScopePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class ScopePolicyTest extends TestCase
{
    use TestDatabase;

    #[DataProvider('viewScopeProvider')]
    #[Test] public function view_scope($role, $isTeamMember, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReportViewer: $isReportViewer, status: $status);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new ScopePolicy())->view($user, $scope);

        $this->assertEquals($expected, $result, $description);
    }

    public static function viewScopeProvider(): array
    {
        // role, isTeamMember, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can view scope'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can view scope'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, true, 'Reviewer can view scope'],
            [null, true, false, ProjectStatus::NotStarted, true, 'Team member can view scope'],
            [null, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot view in-progress scope'],
            [null, false, true, ProjectStatus::Completed, true, 'Report viewer can view completed scope'],
            [Roles::TeamAdmin, false, false, ProjectStatus::Completed, false, 'Non-member cannot view scope'],
        ];
    }

    #[DataProvider('createScopeProvider')]
    #[Test] public function create_scope($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);

        $result = (new ScopePolicy())->create($user, $project);

        $this->assertEquals($expected, $result, $description);
    }

    public static function createScopeProvider(): array
    {
        // role, isTeamMember, isReviewer, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, true, 'Site admin can create scope in-progress project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, true, 'Team admin can create scope in-progress project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, true, 'Reviewer can create scope in-progress project'],
            [null, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot create scope in-progress project'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot create scope'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot create scope'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::Completed, false, 'Site admin cannot create scope in completed project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::Completed, false, 'Team admin cannot create scope in completed project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::Completed, false, 'Reviewer cannot create scope in completed project'],
        ];
    }

    #[DataProvider('deleteScopeProvider')]
    #[Test] public function delete_scope($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new ScopePolicy())->delete($user, $scope);

        $this->assertEquals($expected, $result, $description);
    }

    public static function deleteScopeProvider(): array
    {
        // role, isTeamMember, isReviewer, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, true, 'Site admin can delete in-progress scope'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, true, 'Team admin can delete in-progress scope'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, true, 'Reviewer can delete in-progress scope'],
            [Roles::Reviewer, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot delete in-progress scope'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot delete scope'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot delete scope'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::Completed, false, 'Site admin cannot delete completed scope'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::Completed, false, 'Team admin can delete completed scope'],
            [Roles::Reviewer, true, true, false, ProjectStatus::Completed, false, 'Reviewer can delete completed scope'],
        ];
    }
}
