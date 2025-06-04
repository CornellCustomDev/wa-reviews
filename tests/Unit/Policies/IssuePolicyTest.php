<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Issue;
use App\Models\User;
use App\Policies\IssuePolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class IssuePolicyTest extends TestCase
{
    use TestDatabase;

    #[DataProvider('viewIssueProvider')]
    #[Test] public function view_issue($role, $isTeamMember, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new IssuePolicy())->view($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function viewIssueProvider(): array
    {
        // role, isTeamMember, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can view issue'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can view issue'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, true, 'Reviewer can view issue'],
            [null, true, false, ProjectStatus::NotStarted, true, 'Team member can view issue'],
            [null, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot view in-progress issue'],
            [null, false, true, ProjectStatus::Completed, true, 'Report viewer can view completed issue'],
            [Roles::TeamAdmin, false, false, ProjectStatus::Completed, false, 'Non-member cannot view issue'],
        ];
    }

    #[DataProvider('createIssueProvider')]
    #[Test] public function create_issue($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);

        $result = (new IssuePolicy())->create($user, $project);

        $this->assertEquals($expected, $result, $description);
    }

    public static function createIssueProvider(): array
    {
        // role, isTeamMember, isReviewer, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, true, 'Site admin can create issue in-progress project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, true, 'Team admin can create issue in-progress project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, true, 'Reviewer can create issue in-progress project'],
            [null, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot create issue in-progress project'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot create issue'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot create issue'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::Completed, false, 'Site admin cannot create issue in completed project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::Completed, false, 'Team admin cannot create issue in completed project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::Completed, false, 'Reviewer cannot create issue in completed project'],
        ];
    }

    #[DataProvider('deleteIssueProvider')]
    #[Test] public function delete_issue($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new IssuePolicy())->delete($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function deleteIssueProvider(): array
    {
        // role, isTeamMember, isReviewer, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, true, 'Site admin can delete in-progress issue'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, true, 'Team admin can delete in-progress issue'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, true, 'Reviewer can delete in-progress issue'],
            [Roles::Reviewer, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot delete in-progress issue'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot delete issue'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot delete issue'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::Completed, false, 'Site admin cannot delete completed issue'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::Completed, false, 'Team admin cannot delete completed issue'],
            [Roles::Reviewer, true, true, false, ProjectStatus::Completed, false, 'Reviewer cannot delete completed issue'],
        ];
    }

    #[DataProvider('updateIssueStatusProvider')]
    #[Test] public function update_issue_status($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new IssuePolicy())->updateStatus($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function updateIssueStatusProvider(): array
    {
        // role, isTeamMember, isReviewer, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::Completed, true, 'Site admin can update issue status in completed project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::Completed, true, 'Team admin can update issue status in completed project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::Completed, true, 'Reviewer can update issue status in completed project'],
            [null, true, false, false, ProjectStatus::Completed, false, 'Team member cannot update issue status in completed project'],
            [null, false, false, true, ProjectStatus::Completed, true, 'Report viewer can update issue status in completed project'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::Completed, false, 'Non-member cannot update issue status'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, false, 'Site admin cannot update issue status in in-progress project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, false, 'Team admin cannot update issue status in in-progress project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, false, 'Reviewer cannot update issue status in in-progress project'],
            [null, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot update issue status in in-progress project'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot update issue status in in-progress project'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot update issue status in in-progress project'],
        ];
    }

    #[DataProvider('updateIssueNeedsMitigationProvider')]
    #[Test] public function update_issue_needs_mitigation($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new IssuePolicy())->updateNeedsMitigation($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function updateIssueNeedsMitigationProvider(): array
    {
        // role, isTeamMember, isReviewer, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::Completed, true, 'Site admin can update needs mitigation in completed project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::Completed, true, 'Team admin can update needs mitigation in completed project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::Completed, true, 'Reviewer can update needs mitigation in completed project'],
            [null, true, false, false, ProjectStatus::Completed, false, 'Team member cannot update needs mitigation in completed project'],
            [null, false, false, true, ProjectStatus::Completed, false, 'Report viewer cannot update needs mitigation in completed project'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::Completed, false, 'Non-member cannot update needs mitigation'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, false, 'Site admin cannot update needs mitigation in in-progress project'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, false, 'Team admin cannot update needs mitigation in in-progress project'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, false, 'Reviewer cannot update needs mitigation in in-progress project'],
            [null, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot update needs mitigation in in-progress project'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot update needs mitigation in in-progress project'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot update needs mitigation in in-progress project'],
        ];
    }
}
