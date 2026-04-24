<?php

namespace Tests\Unit\Enums;

use App\Enums\IssueStatus;
use App\Enums\ProjectStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IssueStatusTest extends TestCase
{
    #[Test]
    public function for_phase_review_complete(): void
    {
        $options = IssueStatus::forPhase(ProjectStatus::ReviewComplete);
        $values = array_column($options, 'value');
        $this->assertContains('Reviewed', $values);
        $this->assertContains('False Positive', $values);
        $this->assertNotContains('Fixed', $values);
        $this->assertNotContains('Verified Fixed', $values);
    }

    #[Test]
    public function for_phase_customer_response(): void
    {
        $options = IssueStatus::forPhase(ProjectStatus::CustomerResponse);
        $values = array_column($options, 'value');
        $this->assertContains('Reviewed', $values);
        $this->assertContains('Fixed', $values);
        $this->assertContains('Not Being Fixed', $values);
        $this->assertContains('False Positive', $values);
        $this->assertNotContains('Verified Fixed', $values);
        $this->assertNotContains('Not Fixed', $values);
    }

    #[Test]
    public function for_phase_verification_review(): void
    {
        $options = IssueStatus::forPhase(ProjectStatus::VerificationReview);
        $values = array_column($options, 'value');
        $this->assertContains('Verified Fixed', $values);
        $this->assertContains('Not Fixed', $values);
        $this->assertContains('False Positive', $values);
        $this->assertContains('Not Being Fixed', $values);
        $this->assertNotContains('Fixed', $values);
    }

    #[Test]
    public function for_phase_returns_all_for_active_phases(): void
    {
        $inProgressOptions = IssueStatus::forPhase(ProjectStatus::InProgress);
        $this->assertCount(count(IssueStatus::cases()), $inProgressOptions);
    }

    #[Test]
    public function wont_fix_case_renamed_to_not_being_fixed(): void
    {
        $this->assertEquals('Not Being Fixed', IssueStatus::NotBeingFixed->value);
    }

    #[Test]
    public function new_issue_case_exists(): void
    {
        $this->assertEquals('New Issue', IssueStatus::NewIssue->value);
    }

    #[Test]
    public function not_fixed_case_exists(): void
    {
        $this->assertEquals('Not Fixed', IssueStatus::NotFixed->value);
    }
}
