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
    #[Test]
    public function view_issue($role, $isTeamMember, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $result = (new IssuePolicy)->view($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function viewIssueProvider(): array
    {
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::NotStarted, true, 'Site admin can view issue'],
            [Roles::TeamAdmin, true, false, ProjectStatus::NotStarted, true, 'Team admin can view issue'],
            [Roles::Reviewer, true, false, ProjectStatus::NotStarted, true, 'Reviewer can view issue'],
            [null, true, false, ProjectStatus::NotStarted, true, 'Team member can view issue'],
            [null, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot view in-progress issue'],
            [null, false, true, ProjectStatus::ReviewComplete, true, 'Report viewer can view ReviewComplete issue'],
            [null, false, true, ProjectStatus::Closed, true, 'Report viewer can view Closed issue'],
            [Roles::TeamAdmin, false, false, ProjectStatus::Closed, false, 'Non-member cannot view issue'],
        ];
    }

    #[DataProvider('createIssueProvider')]
    #[Test]
    public function create_issue($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);

        $result = (new IssuePolicy)->create($user, $project);

        $this->assertEquals($expected, $result, $description);
    }

    public static function createIssueProvider(): array
    {
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, true, 'Site admin can create issue in-progress'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, true, 'Team admin can create issue in-progress'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, true, 'Reviewer can create issue in-progress'],
            [null, true, false, false, ProjectStatus::InProgress, false, 'Team member cannot create issue'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot create issue'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::ReviewComplete, false, 'Site admin cannot create issue in ReviewComplete'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::ReviewComplete, false, 'Team admin cannot create issue in ReviewComplete'],
            [Roles::Reviewer, true, true, false, ProjectStatus::ReviewComplete, false, 'Reviewer cannot create issue in ReviewComplete'],
        ];
    }

    #[DataProvider('deleteIssueProvider')]
    #[Test]
    public function delete_issue($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $result = (new IssuePolicy)->delete($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function deleteIssueProvider(): array
    {
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, true, 'Site admin can delete in-progress issue'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, true, 'Team admin can delete in-progress issue'],
            [Roles::Reviewer, true, true, false, ProjectStatus::InProgress, true, 'Reviewer can delete in-progress issue'],
            [Roles::Reviewer, true, false, false, ProjectStatus::InProgress, false, 'Non-assigned reviewer cannot delete'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot delete issue'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::ReviewComplete, false, 'Site admin cannot delete ReviewComplete issue'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::ReviewComplete, false, 'Team admin cannot delete ReviewComplete issue'],
            [Roles::Reviewer, true, true, false, ProjectStatus::ReviewComplete, false, 'Reviewer cannot delete ReviewComplete issue'],
        ];
    }

    #[DataProvider('updateIssueStatusProvider')]
    #[Test]
    public function update_issue_status(
        ?Roles $role, bool $isTeamMember, bool $isReviewer, bool $isVerifier,
        bool $isReportViewer, ProjectStatus $status, bool $expected, string $description
    ) {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isVerifier: $isVerifier, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $result = (new IssuePolicy)->updateStatus($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function updateIssueStatusProvider(): array
    {
        // role, isTeamMember, isReviewer, isVerifier, isReportViewer, status, expected, description
        return [
            // ReviewComplete — reviewer and team admin only
            [Roles::Reviewer, true, true, false, false, ProjectStatus::ReviewComplete, true, 'Reviewer can update status in ReviewComplete'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::ReviewComplete, true, 'TeamAdmin can update status in ReviewComplete'],
            [null, false, false, false, true, ProjectStatus::ReviewComplete, false, 'Report viewer cannot update status in ReviewComplete'],
            [null, false, false, true, false, ProjectStatus::ReviewComplete, false, 'Verifier cannot update status in ReviewComplete'],

            // CustomerResponse — report viewer and team admin only
            [null, false, false, false, true, ProjectStatus::CustomerResponse, true, 'Report viewer can update status in CustomerResponse'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::CustomerResponse, true, 'TeamAdmin can update status in CustomerResponse'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::CustomerResponse, false, 'Reviewer cannot update status in CustomerResponse'],
            [null, false, false, true, false, ProjectStatus::CustomerResponse, false, 'Verifier cannot update status in CustomerResponse'],

            // VerificationReview — reviewer, verifier, team admin
            [Roles::Reviewer, true, true, false, false, ProjectStatus::VerificationReview, true, 'Reviewer can update status in VerificationReview'],
            [null, false, false, true, false, ProjectStatus::VerificationReview, true, 'Verifier can update status in VerificationReview'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::VerificationReview, true, 'TeamAdmin can update status in VerificationReview'],
            [null, false, false, false, true, ProjectStatus::VerificationReview, false, 'Report viewer cannot update status in VerificationReview'],

            // Active phases — nobody
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::InProgress, false, 'Site admin cannot update status in InProgress'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::InProgress, false, 'TeamAdmin cannot update status in InProgress'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::InProgress, false, 'Reviewer cannot update status in InProgress'],

            // Closed — nobody
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::Closed, false, 'Site admin cannot update status in Closed'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::Closed, false, 'TeamAdmin cannot update status in Closed'],
        ];
    }

    #[DataProvider('updateIssueNeedsMitigationProvider')]
    #[Test]
    public function update_issue_needs_mitigation($role, $isTeamMember, $isReviewer, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $result = (new IssuePolicy)->updateNeedsMitigation($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function updateIssueNeedsMitigationProvider(): array
    {
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::ReviewComplete, true, 'Site admin can update mitigation in ReviewComplete'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::ReviewComplete, true, 'TeamAdmin can update mitigation in ReviewComplete'],
            [Roles::Reviewer, true, true, false, ProjectStatus::ReviewComplete, true, 'Reviewer can update mitigation in ReviewComplete'],
            [null, true, false, false, ProjectStatus::ReviewComplete, false, 'Team member cannot update mitigation'],
            [null, false, false, true, ProjectStatus::ReviewComplete, false, 'Report viewer cannot update mitigation'],

            [Roles::SiteAdmin, false, false, false, ProjectStatus::InProgress, false, 'Site admin cannot update mitigation in InProgress'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::InProgress, false, 'TeamAdmin cannot update mitigation in InProgress'],
        ];
    }
}
