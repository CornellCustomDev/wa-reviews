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

    #[Test] public function guideline_instance_not_set_without_guideline()
    {
        $project = Project::factory()->create();

        $issue = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => null,
        ]);

        $this->assertNull($issue->guideline_instance);
    }

    #[Test] public function guideline_instance_is_set_on_creation()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();

        $issue = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);

        $this->assertEquals(1, $issue->guideline_instance);
    }

    #[Test] public function guideline_instance_is_assigned_next_available()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);

        $issue = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);

        $this->assertEquals(2, $issue->guideline_instance);
    }

    #[Test] public function set_guideline_instance_throws_if_duplicate()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);

        $this->expectException(InvalidArgumentException::class);

        // Attempting to set the same instance that already exists
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
            'guideline_instance' => 1,
        ]);
    }

    #[Test] public function set_guideline_instance_throws_if_gap()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);

        $this->expectException(InvalidArgumentException::class);

        // Attempting to set an instance that skips a number
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
            'guideline_instance' => 3,
        ]);
    }

    #[Test] public function set_guideline_instance_allows_resequence()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        $issue1 = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);
        $issue2 = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);
        $issue3 = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);
        $issue1->delete();

        $issue3->guideline_instance = 1;
        $issue3->save();

        $this->assertEquals(1, $issue3->guideline_instance);
    }

    #[Test] public function guideline_instance_does_nothing_if_unchanged()
    {
        $project = Project::factory()->create();
        $guideline = Guideline::factory()->create();
        $issue1 = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline->id,
        ]);

        $issue1->guideline_instance = 1;
        $issue1->save();

        $this->assertEquals(1, $issue1->guideline_instance);
    }

    #[Test] public function guideline_instance_is_updated_on_guideline_change()
    {
        $project = Project::factory()->create();
        $guideline1 = Guideline::factory()->create();
        $guideline2 = Guideline::factory()->create();
        Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline1->id,
        ]);
        $issue = Issue::factory()->create([
            'project_id' => $project->id,
            'guideline_id' => $guideline1->id,
        ]);
        $this->assertEquals(2, $issue->guideline_instance);

        $issue->guideline_id = $guideline2->id;
        $issue->save();

        $this->assertEquals(1, $issue->guideline_instance);
    }
}
