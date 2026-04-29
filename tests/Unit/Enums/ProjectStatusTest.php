<?php

namespace Tests\Unit\Enums;

use App\Enums\ProjectStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProjectStatusTest extends TestCase
{
    #[Test] public function backing_values_are_snake_case(): void
    {
        $this->assertSame('not_started', ProjectStatus::NotStarted->value);
        $this->assertSame('in_progress', ProjectStatus::InProgress->value);
        $this->assertSame('review_complete', ProjectStatus::ReviewComplete->value);
    }

    #[Test] public function label_returns_human_readable_string(): void
    {
        $this->assertSame('Not started', ProjectStatus::NotStarted->label());
        $this->assertSame('In progress', ProjectStatus::InProgress->label());
        $this->assertSame('Review complete', ProjectStatus::ReviewComplete->label());
    }

    #[Test] public function to_select_array_uses_snake_case_value_and_label_for_option(): void
    {
        $array = ProjectStatus::toSelectArray();

        $notStarted = collect($array)->firstWhere('value', 'not_started');
        $this->assertNotNull($notStarted, 'not_started entry missing');
        $this->assertSame('not_started', $notStarted['value']);
        $this->assertSame('Not started', $notStarted['label']);

        $inProgress = collect($array)->firstWhere('value', 'in_progress');
        $this->assertNotNull($inProgress, 'in_progress entry missing');
        $this->assertSame('in_progress', $inProgress['value']);
        $this->assertSame('In progress', $inProgress['label']);

        $reviewComplete = collect($array)->firstWhere('value', 'review_complete');
        $this->assertNotNull($reviewComplete, 'review_complete entry missing');
        $this->assertSame('review_complete', $reviewComplete['value']);
        $this->assertSame('Review complete', $reviewComplete['label']);
    }

    #[Test]
    public function new_cases_exist(): void
    {
        $this->assertEquals('review_complete', ProjectStatus::ReviewComplete->value);
        $this->assertEquals('customer_response', ProjectStatus::CustomerResponse->value);
        $this->assertEquals('verification_review', ProjectStatus::VerificationReview->value);
        $this->assertEquals('closed', ProjectStatus::Closed->value);
    }

    #[Test]
    public function next_status_progression(): void
    {
        $this->assertSame(ProjectStatus::InProgress, ProjectStatus::NotStarted->nextStatus());
        $this->assertSame(ProjectStatus::ReviewComplete, ProjectStatus::InProgress->nextStatus());
        // $this->assertSame(ProjectStatus::CustomerResponse, ProjectStatus::ReviewComplete->nextStatus());
        // $this->assertSame(ProjectStatus::VerificationReview, ProjectStatus::CustomerResponse->nextStatus());
         $this->assertSame(ProjectStatus::VerificationReview, ProjectStatus::ReviewComplete->nextStatus());
        $this->assertSame(ProjectStatus::Closed, ProjectStatus::VerificationReview->nextStatus());
        $this->assertSame(ProjectStatus::Closed, ProjectStatus::Closed->nextStatus());
    }

    #[Test]
    public function previous_status_progression(): void
    {
        $this->assertSame(ProjectStatus::NotStarted, ProjectStatus::InProgress->previousStatus());
        $this->assertSame(ProjectStatus::InProgress, ProjectStatus::ReviewComplete->previousStatus());
        $this->assertSame(ProjectStatus::ReviewComplete, ProjectStatus::VerificationReview->previousStatus());
        // $this->assertSame(ProjectStatus::ReviewComplete, ProjectStatus::CustomerResponse->previousStatus());
        // $this->assertSame(ProjectStatus::CustomerResponse, ProjectStatus::VerificationReview->previousStatus());
        $this->assertSame(ProjectStatus::VerificationReview, ProjectStatus::Closed->previousStatus());
    }

    #[Test]
    public function active_cases_returns_not_started_and_in_progress(): void
    {
        $this->assertEquals([ProjectStatus::NotStarted, ProjectStatus::InProgress], ProjectStatus::activeCases());
    }

    #[Test]
    public function reviewed_cases_returns_post_review_phases(): void
    {
        $this->assertEquals(
            [ProjectStatus::ReviewComplete, ProjectStatus::CustomerResponse, ProjectStatus::VerificationReview],
            ProjectStatus::reviewedCases()
        );
    }

    #[Test]
    public function completed_cases_returns_closed(): void
    {
        $this->assertEquals([ProjectStatus::Closed], ProjectStatus::completedCases());
    }

    #[Test]
    public function is_post_review_returns_true_for_reviewed_and_completed_cases(): void
    {
        $this->assertFalse(ProjectStatus::NotStarted->isPostReview());
        $this->assertFalse(ProjectStatus::InProgress->isPostReview());
        $this->assertTrue(ProjectStatus::ReviewComplete->isPostReview());
        $this->assertTrue(ProjectStatus::CustomerResponse->isPostReview());
        $this->assertTrue(ProjectStatus::VerificationReview->isPostReview());
        $this->assertTrue(ProjectStatus::Closed->isPostReview());
    }

    #[Test]
    public function is_review_complete(): void
    {
        $this->assertTrue(ProjectStatus::ReviewComplete->isReviewComplete());
        $this->assertFalse(ProjectStatus::InProgress->isReviewComplete());
    }

    #[Test]
    public function is_closed(): void
    {
        $this->assertTrue(ProjectStatus::Closed->isClosed());
        $this->assertFalse(ProjectStatus::VerificationReview->isClosed());
    }

    #[Test]
    public function next_action_label_returns_null_for_closed(): void
    {
        $this->assertNull(ProjectStatus::Closed->nextActionLabel());
    }

    #[Test]
    public function previous_action_label_returns_null_for_not_started(): void
    {
        $this->assertNull(ProjectStatus::NotStarted->previousActionLabel());
    }
}
