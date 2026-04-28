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
        $this->assertSame('Not being fixed', IssueStatus::WontFix->label());
        $this->assertSame('False positive', IssueStatus::FalsePositive->label());
        $this->assertSame('Verified fixed', IssueStatus::Verified->label());
    }

    #[Test] public function to_select_array_uses_snake_case_value_and_label_for_option(): void
    {
        $array = IssueStatus::toSelectArray();

        $reviewed = collect($array)->firstWhere('value', 'reviewed');
        $this->assertNotNull($reviewed, 'reviewed entry missing');
        $this->assertSame('reviewed', $reviewed['value']);
        $this->assertSame('Reviewed', $reviewed['label']);

        $wontFix = collect($array)->firstWhere('value', 'not_being_fixed');
        $this->assertNotNull($wontFix, 'not_being_fixed entry missing');
        $this->assertSame('not_being_fixed', $wontFix['value']);
        $this->assertSame('Not being fixed', $wontFix['label']);

        $verified = collect($array)->firstWhere('value', 'verified_fixed');
        $this->assertNotNull($verified, 'verified_fixed entry missing');
        $this->assertSame('verified_fixed', $verified['value']);
        $this->assertSame('Verified fixed', $verified['label']);
    }

    #[Test]
    public function for_phase_review_complete(): void
    {
        $options = IssueStatus::forPhase(ProjectStatus::ReviewComplete);
        $values = array_column($options, 'value');
        $this->assertContains('reviewed', $values);
        $this->assertContains('false_positive', $values);
        $this->assertNotContains('fixed', $values);
        $this->assertNotContains('verified_fixed', $values);
    }

    #[Test]
    public function for_phase_customer_response(): void
    {
        $options = IssueStatus::forPhase(ProjectStatus::CustomerResponse);
        $values = array_column($options, 'value');
        $this->assertContains('reviewed', $values);
        $this->assertContains('fixed', $values);
        $this->assertContains('not_being_fixed', $values);
        $this->assertContains('false_positive', $values);
        $this->assertNotContains('verified_fixed', $values);
        $this->assertNotContains('not_fixed', $values);
    }

    #[Test]
    public function for_phase_verification_review(): void
    {
        $options = IssueStatus::forPhase(ProjectStatus::VerificationReview);
        $values = array_column($options, 'value');
        $this->assertContains('verified_fixed', $values);
        $this->assertContains('not_fixed', $values);
        $this->assertContains('false_positive', $values);
        $this->assertContains('not_being_fixed', $values);
        $this->assertNotContains('fixed', $values);
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
        $this->assertEquals('not_being_fixed', IssueStatus::WontFix->value);
    }

    #[Test]
    public function new_issue_case_exists(): void
    {
        $this->assertEquals('new_issue', IssueStatus::NewIssue->value);
    }

    #[Test]
    public function not_fixed_case_exists(): void
    {
        $this->assertEquals('not_fixed', IssueStatus::NotFixed->value);
    }
}
