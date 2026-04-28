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
        $this->assertSame('completed', ProjectStatus::Completed->value);
    }

    #[Test] public function label_returns_human_readable_string(): void
    {
        $this->assertSame('Not Started', ProjectStatus::NotStarted->label());
        $this->assertSame('In Progress', ProjectStatus::InProgress->label());
        $this->assertSame('Completed', ProjectStatus::Completed->label());
    }

    #[Test] public function to_select_array_uses_snake_case_value_and_label_for_option(): void
    {
        $array = ProjectStatus::toSelectArray();

        $notStarted = collect($array)->firstWhere('value', 'not_started');
        $this->assertNotNull($notStarted, 'not_started entry missing');
        $this->assertSame('Not Started', $notStarted['option']);
        $this->assertSame('Not Started', $notStarted['label']);

        $inProgress = collect($array)->firstWhere('value', 'in_progress');
        $this->assertNotNull($inProgress, 'in_progress entry missing');
        $this->assertSame('In Progress', $inProgress['option']);
        $this->assertSame('In Progress', $inProgress['label']);

        $completed = collect($array)->firstWhere('value', 'completed');
        $this->assertNotNull($completed, 'completed entry missing');
        $this->assertSame('Completed', $completed['option']);
        $this->assertSame('Completed', $completed['label']);
    }
}
