<?php

namespace Tests\Unit\Policies;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\User;
use App\Policies\CommentPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class CommentPolicyTest extends TestCase
{
    use TestDatabase;

    #[DataProvider('createCommentProvider')]
    #[Test]
    public function create_comment(
        ?Roles $role, bool $isTeamMember, bool $isReviewer, bool $isVerifier,
        bool $isReportViewer, ProjectStatus $status, bool $expected, string $description
    ): void {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReviewer: $isReviewer, isVerifier: $isVerifier, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $result = (new CommentPolicy)->create($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function createCommentProvider(): array
    {
        // role, isTeamMember, isReviewer, isVerifier, isReportViewer, status, expected, description
        return [
            // Nobody can comment in active phases
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::InProgress, false, 'Site admin cannot comment in InProgress'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::InProgress, false, 'Team admin cannot comment in InProgress'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::InProgress, false, 'Reviewer cannot comment in InProgress'],
            [null, false, false, false, true, ProjectStatus::InProgress, false, 'Report viewer cannot comment in InProgress'],

            // Reviewer can comment in reviewedCases
            [Roles::Reviewer, true, true, false, false, ProjectStatus::ReviewComplete, true, 'Reviewer can comment in ReviewComplete'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::CustomerResponse, true, 'Reviewer can comment in CustomerResponse'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::VerificationReview, true, 'Reviewer can comment in VerificationReview'],

            // Verifier (assigned) can comment in reviewedCases
            [Roles::Reviewer, true, false, true, false, ProjectStatus::ReviewComplete, true, 'Verifier can comment in ReviewComplete'],
            [Roles::Reviewer, true, false, true, false, ProjectStatus::VerificationReview, true, 'Verifier can comment in VerificationReview'],

            // Team admin can comment in reviewedCases
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::ReviewComplete, true, 'Team admin can comment in ReviewComplete'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::CustomerResponse, true, 'Team admin can comment in CustomerResponse'],

            // Report viewer (customer) can comment in reviewedCases
            [null, false, false, false, true, ProjectStatus::ReviewComplete, true, 'Report viewer can comment in ReviewComplete'],
            [null, false, false, false, true, ProjectStatus::CustomerResponse, true, 'Report viewer can comment in CustomerResponse'],
            [null, false, false, false, true, ProjectStatus::VerificationReview, true, 'Report viewer can comment in VerificationReview'],

            // Nobody can comment when Closed
            [Roles::SiteAdmin, false, false, false, false, ProjectStatus::Closed, false, 'Site admin cannot comment when Closed'],
            [Roles::TeamAdmin, true, false, false, false, ProjectStatus::Closed, false, 'Team admin cannot comment when Closed'],
            [Roles::Reviewer, true, true, false, false, ProjectStatus::Closed, false, 'Reviewer cannot comment when Closed'],
            [null, false, false, false, true, ProjectStatus::Closed, false, 'Report viewer cannot comment when Closed'],

            // Non-members cannot comment
            [null, false, false, false, false, ProjectStatus::ReviewComplete, false, 'Non-member cannot comment'],
        ];
    }

    #[Test]
    public function author_can_edit_comment_before_10_minutes(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subMinutes(9)->subSeconds(59),
        ]);

        $result = (new CommentPolicy)->update($user, $comment);

        $this->assertTrue($result);
    }

    #[Test]
    public function author_cannot_edit_comment_after_10_minutes(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subMinutes(10)->subSecond(),
        ]);

        $result = (new CommentPolicy)->update($user, $comment);

        $this->assertFalse($result);
    }

    #[Test]
    public function non_author_cannot_edit_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $author->id,
            'created_at' => now()->subMinutes(2),
        ]);

        $result = (new CommentPolicy)->update($other, $comment);

        $this->assertFalse($result);
    }

    #[Test]
    public function author_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $result = (new CommentPolicy)->delete($user, $comment);

        $this->assertTrue($result);
    }

    #[Test]
    public function site_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create();
        $admin->addRole(Roles::SiteAdmin);
        $other = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $other->id]);

        $result = (new CommentPolicy)->delete($admin, $comment);

        $this->assertTrue($result);
    }

    #[Test]
    public function non_author_non_admin_cannot_delete_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id]);

        $result = (new CommentPolicy)->delete($other, $comment);

        $this->assertFalse($result);
    }
}
