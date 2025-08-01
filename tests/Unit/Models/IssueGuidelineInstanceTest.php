<?php

namespace Tests\Unit\Models;

use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Project;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Unit\TestDatabase;

class IssueGuidelineInstanceTest extends TestCase
{
    use TestDatabase;

    #[Test] public function set_guideline_instance_assigns_next_available()
    {
        $issue = $this->makeIssues(1);

        $issue->setGuidelineInstance();

        $this->assertEquals(2, $issue->guideline_instance);
    }

    #[Test] public function set_guideline_instance_throws_if_duplicate()
    {
        $issue = $this->makeIssues(1);

        $this->expectException(InvalidArgumentException::class);

        // Attempting to set the same instance that already exists
        $issue->setGuidelineInstance(1);
    }

    #[Test] public function set_guideline_instance_throws_if_gap()
    {
        $issue = $this->makeIssues(2);

        $this->expectException(InvalidArgumentException::class);

        // Attempting to set an instance that skips a number
        $issue->setGuidelineInstance(3);
    }

    #[Test] public function set_guideline_instance_allows_resequence()
    {
        $issue = $this->makeIssues(2);

        $issue->setGuidelineInstance(1);

        $this->assertEquals(1, $issue->guideline_instance);
    }

    #[Test] public function set_guideline_instance_does_nothing_if_same()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        $issue = Issue::factory()->make([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
            'guideline_instance' => 3,
        ]);
        $issue->project = $project;
        $issue->setGuidelineInstance(3);
        $this->assertEquals(3, $issue->guideline_instance);
    }

    #[Test] public function set_guideline_instance_does_nothing_without_guideline()
    {
        $project = Project::factory()->create();
        $issue = Issue::factory()->make([
            'project_id' => $project->id,
            'guideline_id' => null,
        ]);
        $issue->project = $project;
        $issue->setGuidelineInstance();
        $this->assertNull($issue->guideline_instance);
    }

    private function makeIssues(int $guideline_instance): Issue
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
            'guideline_instance' => $guideline_instance,
        ]);
        $issue = Issue::factory()->make([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);
        $issue->project = $project;

        return $issue;
    }
}
