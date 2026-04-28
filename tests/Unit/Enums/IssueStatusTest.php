<?php

namespace Tests\Unit\Enums;

use App\Enums\IssueStatus;
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

        $wontFix = collect($array)->firstWhere('value', 'not_being_fixed');
        $this->assertNotNull($wontFix, 'not_being_fixed entry missing');
        $this->assertSame('Not Being Fixed', $wontFix['option']);

        $verified = collect($array)->firstWhere('value', 'verified_fixed');
        $this->assertNotNull($verified, 'verified_fixed entry missing');
        $this->assertSame('Verified Fixed', $verified['option']);
    }
}
