<?php

namespace Tests\Feature\Evals;

use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Services\Evals\Graders\StructuralGrader;
use Tests\Feature\FeatureTestCase;

class StructuralGraderTest extends FeatureTestCase
{
    public function test_a_guideline_entry_that_is_not_an_object_fails_schema_validity(): void
    {
        $scores = $this->grade(['guidelines' => ['invalid'], 'feedback' => 'ok']);

        $this->assertFalse($scores['schema_valid']);
        $this->assertSame(['guidelines has 1 entries that are not objects'], $scores['schema_issues']);
        $this->assertSame(0, $scores['guideline_count']);
    }

    public function test_a_guidelines_object_in_place_of_a_list_fails_schema_validity(): void
    {
        $scores = $this->grade(['guidelines' => ['first' => ['number' => 1]], 'feedback' => 'ok']);

        $this->assertFalse($scores['schema_valid']);
        $this->assertContains('guidelines is not a list', $scores['schema_issues']);
    }

    public function test_an_empty_list_with_feedback_is_schema_valid(): void
    {
        $scores = $this->grade(['guidelines' => [], 'feedback' => 'Nothing to report.']);

        $this->assertTrue($scores['schema_valid']);
        $this->assertSame([], $scores['schema_issues']);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function grade(array $payload): array
    {
        $case = EvalCase::factory()->structuredOutput()->create(['dataset_version' => 'structural-test']);
        $result = EvalResult::factory()->create([
            'eval_case_id' => $case->id,
            'output' => ['text' => json_encode($payload), 'structured' => $payload],
        ]);

        return app(StructuralGrader::class)->grade($case, $result);
    }
}
