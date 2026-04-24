<?php

namespace Tests\Feature\Livewire;

use App\Enums\ProjectStatus;
use App\Enums\Roles;
use App\Livewire\Comments\Comments;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Scope;
use App\Models\User;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

class CommentTest extends FeatureTestCase
{
    #[Test]
    public function team_member_can_add_comment_to_issue(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id, 'status' => ProjectStatus::ReviewComplete]);
        $project->assignToUser($user);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->set('newComment', 'This is a test comment.')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_type' => Issue::class,
            'commentable_id' => $issue->id,
            'body' => 'This is a test comment.',
        ]);
    }

    #[Test]
    public function report_viewer_can_add_comment_to_completed_project_issue(): void
    {
        $admin = $this->getLoggedInTestUser([Roles::TeamAdmin]);
        $viewer = User::factory()->create();
        $team = $admin->teams()->first();
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'status' => ProjectStatus::ReviewComplete,
        ]);
        $project->addReportViewer($viewer);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        $this->actingAs($viewer);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->set('newComment', 'Question about this issue.')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'user_id' => $viewer->id,
            'body' => 'Question about this issue.',
        ]);
    }

    #[Test]
    public function author_can_edit_comment_within_10_minutes(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $issue = Issue::factory()->create(['project_id' => $project->id]);
        $comment = Comment::factory()->for($issue, 'commentable')->create([
            'user_id' => $user->id,
            'body' => 'Original text.',
            'created_at' => now()->subMinutes(5),
        ]);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->call('showEdit', $comment->id)
            ->set('editBody', 'Updated text.')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'body' => 'Updated text.']);
    }

    #[Test]
    public function author_cannot_edit_comment_after_10_minutes(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $issue = Issue::factory()->create(['project_id' => $project->id]);
        $comment = Comment::factory()->for($issue, 'commentable')->create([
            'user_id' => $user->id,
            'body' => 'Original text.',
            'created_at' => now()->subMinutes(10)->subSecond(),
        ]);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->call('showEdit', $comment->id)
            ->assertForbidden();
    }

    #[Test]
    public function author_can_delete_own_comment(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $issue = Issue::factory()->create(['project_id' => $project->id]);
        $comment = Comment::factory()->for($issue, 'commentable')->create(['user_id' => $user->id]);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->call('deleteComment', $comment->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    #[Test]
    public function non_author_cannot_delete_another_users_comment(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $issue = Issue::factory()->create(['project_id' => $project->id]);
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->for($issue, 'commentable')->create(['user_id' => $otherUser->id]);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->call('deleteComment', $comment->id)
            ->assertForbidden();
    }

    #[Test]
    public function team_member_can_add_comment_to_scope(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id, 'status' => ProjectStatus::ReviewComplete]);
        $project->assignToUser($user);
        $scope = Scope::factory()->create(['project_id' => $project->id]);

        Livewire::test(Comments::class, ['commentable' => $scope])
            ->set('newComment', 'Scope comment.')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => Scope::class,
            'commentable_id' => $scope->id,
            'body' => 'Scope comment.',
        ]);
    }

    #[Test]
    public function save_edit_without_active_edit_session_does_nothing(): void
    {
        $user = $this->getLoggedInTestUser([Roles::Reviewer]);
        $team = $user->teams()->first();
        $project = Project::factory()->create(['team_id' => $team->id]);
        $issue = Issue::factory()->create(['project_id' => $project->id]);

        Livewire::test(Comments::class, ['commentable' => $issue])
            ->call('saveEdit')
            ->assertHasNoErrors();
    }
}
