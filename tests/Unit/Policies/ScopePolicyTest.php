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
    #[Test]
    public function view_scope($role, $isTeamMember, $isVerifier, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isVerifier: $isVerifier, isReportViewer: $isReportViewer, status: $status);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new ScopePolicy)->view($user, $scope);

        $this->assertEquals($expected, $result, $description);
    }

    public static function viewScopeProvider(): array
    {
        // role, isTeamMember, isVerifier, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, ProjectStatus::NotStarted, true, 'Site admin can view scope'],
            [Roles::TeamAdmin, true, false, false, ProjectStatus::NotStarted, true, 'Team admin can view scope'],
            [Roles::Reviewer, true, false, false, ProjectStatus::NotStarted, true, 'Reviewer can view scope'],
            [Roles::Reviewer, true, true, false, ProjectStatus::NotStarted, true, 'Verifier can view scope'],
            [null, true, false, false, ProjectStatus::NotStarted, true, 'Team member can view scope'],
            [null, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot view in-progress scope'],
            [null, false, false, true, ProjectStatus::ReviewComplete, true, 'Report viewer can view ReviewComplete scope'],
            [Roles::Reviewer, true, true, false, ProjectStatus::ReviewComplete, true, 'Verifier can view scope'],
            [Roles::TeamAdmin, false, false, false, ProjectStatus::ReviewComplete, false, 'Non-member cannot view scope'],
        ];
    }

    #[DataProvider('createScopeProvider')]
    #[Test]
    public function create_scope($role, $isTeamMember, $isReviewer, $isVerifier, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isVerifier: $isVerifier, isReportViewer: $isReportViewer, status: $status);

        $result = (new ScopePolicy)->create($user, $project);

        $this->assertEquals($expected, $result, $description);
    }

    public static function createScopeProvider(): array
    {
        // role, isTeamMember, isReviewer, isVerifier, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::InProgress, true, 'Site admin can create scope in-progress project'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::InProgress, true, 'Team admin can create scope in-progress project'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::InProgress, true, 'Reviewer can create scope in-progress project'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::InProgress, false, 'Verifier cannot create scope in in-progress project'],
            [null, true, false, false, false, ProjectStatus::InProgress, false, 'Team member cannot create scope in-progress project'],
            [null, false, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot create scope'],
            [Roles::TeamAdmin, false, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot create scope'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::ReviewComplete, false, 'Site admin cannot create scope in ReviewComplete project'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::ReviewComplete, false, 'Team admin cannot create scope in ReviewComplete project'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::ReviewComplete, false, 'Reviewer cannot create scope in ReviewComplete project'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::ReviewComplete, false, 'Verifier cannot create scope in ReviewComplete project'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::VerificationReview, false, 'Site admin cannot create scope in VerificationReview project'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::VerificationReview, false, 'Team admin cannot create scope in VerificationReview project'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::VerificationReview, false, 'Reviewer cannot create scope in VerificationReview project'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::VerificationReview, true, 'Verifier can create scope in VerificationReview project'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::Closed, false, 'Site admin cannot create scope in closed project'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::Closed, false, 'Team admin cannot create scope in closed project'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::Closed, false, 'Reviewer cannot create scope in closed project'],
        ];
    }

    #[DataProvider('updateScopeProvider')]
    #[Test]
    public function update_scope($role, $isTeamMember, $isReviewer, $isVerifier, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isVerifier: $isVerifier, isReportViewer: $isReportViewer, status: $status);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new ScopePolicy)->update($user, $scope);

        $this->assertEquals($expected, $result, $description);
    }

    public static function updateScopeProvider(): array
    {
        // role, isTeamMember, isReviewer, isVerifier, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::InProgress, true, 'Site admin can update in-progress scope'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::InProgress, true, 'Team admin can update in-progress scope'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::InProgress, true, 'Reviewer can update in-progress scope'],
            [Roles::Reviewer, true, false, false, false, ProjectStatus::InProgress, false, 'Team member cannot update in-progress scope'],
            [null, false, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot update scope'],
            [Roles::TeamAdmin, false, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot update scope'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::ReviewComplete, false, 'Site admin cannot update ReviewComplete project scope'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::ReviewComplete, false, 'Team admin cannot update ReviewComplete project scope'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::ReviewComplete, false, 'Reviewer cannot update ReviewComplete project scope'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::ReviewComplete, false, 'Verifier cannot update ReviewComplete project scope'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::Closed, false, 'Site admin cannot update closed project scope'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::Closed, false, 'Team admin cannot update closed project scope'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::Closed, false, 'Reviewer cannot update closed project scope'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::Closed, false, 'Verifier cannot update closed project scope'],
        ];
    }

    #[DataProvider('deleteScopeProvider')]
    #[Test]
    public function delete_scope($role, $isTeamMember, $isReviewer, $isVerifier, $isReportViewer, $status, $expected, $description)
    {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isVerifier: $isVerifier, isReportViewer: $isReportViewer, status: $status);
        $scope = Scope::factory()->create([
            'project_id' => $project->id,
        ]);

        $result = (new ScopePolicy)->delete($user, $scope);

        $this->assertEquals($expected, $result, $description);
    }

    public static function deleteScopeProvider(): array
    {
        // role, isTeamMember, isReviewer, isVerifier, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::InProgress, true, 'Site admin can delete in-progress scope'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::InProgress, true, 'Team admin can delete in-progress scope'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::InProgress, true, 'Reviewer can delete in-progress scope'],
            [Roles::Reviewer, true, false, false, false, ProjectStatus::InProgress, false, 'Team member cannot delete in-progress scope'],
            [null, false, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot delete scope'],
            [Roles::TeamAdmin, false, false, false, false, ProjectStatus::InProgress, false, 'Non-member cannot delete scope'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::ReviewComplete, false, 'Site admin cannot delete ReviewComplete project scope'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::ReviewComplete, false, 'Team admin cannot delete ReviewComplete project scope'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::ReviewComplete, false, 'Reviewer cannot delete ReviewComplete project scope'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::ReviewComplete, false, 'Verifier cannot delete ReviewComplete project scope'],

            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::Closed, false, 'Site admin cannot delete closed project scope'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::Closed, false, 'Team admin cannot delete closed project scope'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::Closed, false, 'Reviewer cannot delete closed project scope'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::Closed, false, 'Verifier cannot delete closed project scope'],
        ];
    }
}
