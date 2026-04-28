<?php

namespace Tests\Unit\Enums;

use App\Enums\IssueStatus;
use App\Enums\ProjectStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IssueStatusTest extends TestCase
{
    #[Test] public function backing_values_are_snake_case(): void
    {
        $this->assertSame('reviewed', IssueStatus::Reviewed->value);
        $this->assertSame('fixed', IssueStatus::Fixed->value);
        $this->assertSame('not_being_fixed', IssueStatus::WontFix->value);
        $this->assertSame('false_positive', IssueStatus::FalsePositive->value);
        $this->assertSame('verified_fixed', IssueStatus::Verified->value);
    }

    #[Test] public function label_returns_human_readable_string(): void
    {
        $this->assertSame('Reviewed', IssueStatus::Reviewed->label());
        $this->assertSame('Fixed', IssueStatus::Fixed->label());
        $this->assertSame('Not Being Fixed', IssueStatus::WontFix->label());
        $this->assertSame('False Positive', IssueStatus::FalsePositive->label());
        $this->assertSame('Verified Fixed', IssueStatus::Verified->label());
    }

    #[Test] public function to_select_array_uses_snake_case_value_and_label_for_option(): void
    {
        $array = IssueStatus::toSelectArray();

        $reviewed = collect($array)->firstWhere('value', 'reviewed');
        $this->assertNotNull($reviewed, 'reviewed entry missing');
        $this->assertSame('Reviewed', $reviewed['option']);
        $this->assertSame('Reviewed', $reviewed['label']);

        $wontFix = collect($array)->firstWhere('value', 'not_being_fixed');
        $this->assertNotNull($wontFix, 'not_being_fixed entry missing');
        $this->assertSame('Not Being Fixed', $wontFix['option']);
        $this->assertSame('Not Being Fixed', $wontFix['label']);

        $verified = collect($array)->firstWhere('value', 'verified_fixed');
        $this->assertNotNull($verified, 'verified_fixed entry missing');
        $this->assertSame('Verified Fixed', $verified['option']);
        $this->assertSame('Verified Fixed', $verified['label']);
    }

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
