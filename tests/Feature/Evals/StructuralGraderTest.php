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

    public function test_a_guideline_field_of_the_wrong_type_fails_schema_validity(): void
    {
        $scores = $this->grade(['guidelines' => [$this->guideline(['target' => ['nav', 'footer'], 'number' => '12'])], 'feedback' => 'ok']);

        $this->assertFalse($scores['schema_valid']);
        $this->assertSame([
            'guideline 0: target is not of type string',
            'guideline 0: number is not of type integer',
        ], $scores['schema_issues']);
    }

    public function test_fields_the_schema_does_not_declare_fail_schema_validity(): void
    {
        $scores = $this->grade(['guidelines' => [$this->guideline(['confidence' => 0.9])], 'feedback' => 'ok', 'notes' => 'extra']);

        $this->assertFalse($scores['schema_valid']);
        $this->assertSame([
            'unexpected field notes',
            'guideline 0: unexpected field confidence',
        ], $scores['schema_issues']);
    }

    public function test_a_complete_guideline_of_the_declared_types_is_schema_valid(): void
    {
        $scores = $this->grade(['guidelines' => [$this->guideline()], 'feedback' => 'ok']);

        $this->assertTrue($scores['schema_valid']);
        $this->assertSame([], $scores['schema_issues']);
    }

    public function test_an_empty_list_with_feedback_is_schema_valid(): void
    {
        $scores = $this->grade(['guidelines' => [], 'feedback' => 'Nothing to report.']);

        $this->assertTrue($scores['schema_valid']);
        $this->assertSame([], $scores['schema_issues']);
    }

    public function test_combined_numbers_are_counted_from_the_recommenders_own_text(): void
    {
        $text = json_encode(['guidelines' => [
            ['number' => '33 and 34'],
            ['number' => '35, 36'],
            ['number' => '45'],
        ]]);
        $collapsed = ['guidelines' => [['number' => 33], ['number' => 35], ['number' => 45]], 'feedback' => ''];

        $case = EvalCase::factory()->create(['dataset_version' => 'structural-test']);
        $result = EvalResult::factory()->create([
            'eval_case_id' => $case->id,
            'output' => ['text' => $text, 'structured' => $collapsed],
        ]);

        $scores = app(StructuralGrader::class)->grade($case, $result);

        $this->assertSame(2, $scores['combined_number_count'], 'Counted before StructuredOutput collapses them.');
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

    /**
     * A guideline with every schema field populated with a value of its declared type.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function guideline(array $overrides = []): array
    {
        return [
            'target' => 'Main navigation',
            'reasoning' => 'The images carry meaning.',
            'number' => 12,
            'heading' => 'Images need alt text',
            'criteria' => '1.1.1 Non-text Content',
            'assessment' => 'Fail',
            'observation' => 'The logo has no alt attribute.',
            'recommendation' => 'Add descriptive alt text.',
            'testing' => 'Inspect the img element.',
            'impact' => 'Serious',
            ...$overrides,
        ];
    }
}
