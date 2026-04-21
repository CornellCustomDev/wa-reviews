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

    // --- create ---

    #[DataProvider('createCommentProvider')]
    #[Test] public function create_comment(
        Roles|null $role, bool $isTeamMember, bool $isReportViewer,
        ProjectStatus $status, bool $expected, string $description
    ): void {
        $user = User::factory()->create();
        $projectTeam = $this->setupTeam($user, $isTeamMember, $role);
        $project = $this->setupProject($projectTeam, $user, isReportViewer: $isReportViewer, status: $status);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $result = (new CommentPolicy())->create($user, $issue);

        $this->assertEquals($expected, $result, $description);
    }

    public static function createCommentProvider(): array
    {
        // role, isTeamMember, isReportViewer, status, expected, description
        return [
            [Roles::SiteAdmin, false, false, ProjectStatus::InProgress, true, 'Site admin can comment'],
            [Roles::TeamAdmin, true, false, ProjectStatus::InProgress, true, 'Team admin can comment'],
            [Roles::Reviewer, true, false, ProjectStatus::InProgress, true, 'Reviewer can comment'],
            [null, true, false, ProjectStatus::InProgress, true, 'Team member can comment'],
            [null, false, true, ProjectStatus::Completed, true, 'Report viewer of completed project can comment'],
            [null, false, false, ProjectStatus::InProgress, false, 'Non-member cannot comment'],
            [null, false, true, ProjectStatus::InProgress, false, 'Report viewer of in-progress project cannot comment'],
        ];
    }

    // --- update ---

    #[Test] public function author_can_edit_comment_within_10_minutes(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subMinutes(5),
        ]);

        $result = (new CommentPolicy())->update($user, $comment);

        $this->assertTrue($result);
    }

    #[Test] public function author_cannot_edit_comment_after_10_minutes(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $user->id,
            'created_at' => now()->subMinutes(11),
        ]);

        $result = (new CommentPolicy())->update($user, $comment);

        $this->assertFalse($result);
    }

    #[Test] public function non_author_cannot_edit_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id' => $author->id,
            'created_at' => now()->subMinutes(2),
        ]);

        $result = (new CommentPolicy())->update($other, $comment);

        $this->assertFalse($result);
    }

    // --- delete ---

    #[Test] public function author_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user->id]);

        $result = (new CommentPolicy())->delete($user, $comment);

        $this->assertTrue($result);
    }

    #[Test] public function site_admin_can_delete_any_comment(): void
    {
        $admin = User::factory()->create();
        $admin->addRole(Roles::SiteAdmin);
        $other = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $other->id]);

        $result = (new CommentPolicy())->delete($admin, $comment);

        $this->assertTrue($result);
    }

    #[Test] public function non_author_non_admin_cannot_delete_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $author->id]);

        $result = (new CommentPolicy())->delete($other, $comment);

        $this->assertFalse($result);
    }
}
