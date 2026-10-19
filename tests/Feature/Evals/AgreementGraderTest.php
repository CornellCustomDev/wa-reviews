<?php

namespace Tests\Feature\Evals;

use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use App\Services\Evals\Graders\AgreementGrader;
use Tests\Feature\FeatureTestCase;

class AgreementGraderTest extends FeatureTestCase
{
    use MakesEvalFixtures;

    public function test_it_scores_precision_recall_and_f1_against_reviewer_verdicts(): void
    {
        [$accepted, $modified, $rejected, $untriaged, $missed] = $this->guidelineIds(5);

        $case = $this->recommenderCase([
            'accepted_guideline_ids' => [$accepted],
            'modified_guideline_ids' => [$modified],
            'rejected_guideline_ids' => [$rejected],
            'untriaged_guideline_ids' => [$untriaged],
            'missed_guideline_ids' => [$missed],
        ]);

        $scores = $this->grade($case, [$accepted, $modified, $rejected, $untriaged]);

        $this->assertTrue($scores['agreement_scored']);
        $this->assertSame(4, $scores['returned_count']);
        $this->assertSame(3, $scores['scoreable_count'], 'The untriaged id carries no verdict, so it cannot be scored.');
        $this->assertSame(1, $scores['untriaged_returned_count']);

        // Strict: only `accepted` is a hit, so 1 of 3 judged ids is right, and of
        // the two ids a reviewer endorsed (accepted + the one they added
        // themselves) the model found one.
        $this->assertSame(0.3333, $scores['precision_strict']);
        $this->assertSame(0.5, $scores['recall_strict']);
        $this->assertSame(0.4, $scores['f1_strict']);
        $this->assertSame(1, $scores['true_positives_strict']);
        $this->assertSame(2, $scores['false_positives_strict']);
        $this->assertSame(1, $scores['false_negatives_strict']);

        // Lenient: `modified` counts as a hit too — 2 of 3 right, 2 of 3 found.
        $this->assertSame(0.6667, $scores['precision_lenient']);
        $this->assertSame(0.6667, $scores['recall_lenient']);
        $this->assertSame(0.6667, $scores['f1_lenient']);
        $this->assertSame(1, $scores['false_positives_lenient']);
    }

    public function test_an_id_no_reviewer_judged_is_not_counted_against_precision(): void
    {
        [$accepted, $novel] = $this->guidelineIds(2);

        $case = $this->recommenderCase(['accepted_guideline_ids' => [$accepted]]);

        $scores = $this->grade($case, [$accepted, $novel]);

        $this->assertSame(1, $scores['unlabelled_returned_count']);
        $this->assertSame(1.0, $scores['precision_strict'], 'A guideline the historical model never offered is not evidence either way.');
        $this->assertSame(1, $scores['scoreable_count']);
    }

    public function test_returning_nothing_scoreable_on_a_case_with_expected_guidelines_scores_zero_f1(): void
    {
        [$accepted, $novel] = $this->guidelineIds(2);

        $case = $this->recommenderCase(['accepted_guideline_ids' => [$accepted]]);

        $scores = $this->grade($case, [$novel]);

        $this->assertNull($scores['precision_strict']);
        $this->assertSame(0.0, $scores['recall_strict']);
        $this->assertSame(0.0, $scores['f1_strict'], 'An empty answer must not drop the case from the F1 comparison.');
        $this->assertSame(0.0, $scores['f1_lenient']);
    }

    public function test_f1_is_undefined_when_no_guideline_was_expected(): void
    {
        [$rejected] = $this->guidelineIds(1);

        $case = $this->recommenderCase(['rejected_guideline_ids' => [$rejected]]);

        $scores = $this->grade($case, [$rejected]);

        $this->assertSame(0.0, $scores['precision_strict']);
        $this->assertNull($scores['recall_strict']);
        $this->assertNull($scores['f1_strict']);
    }

    public function test_pre_existing_guidelines_are_excluded_rather_than_penalised(): void
    {
        [$accepted, $preExisting] = $this->guidelineIds(2);

        $case = $this->recommenderCase([
            'accepted_guideline_ids' => [$accepted],
            'rejected_guideline_ids' => [$preExisting],
            'pre_existing_guideline_ids' => [$preExisting],
        ]);

        $scores = $this->grade($case, [$accepted, $preExisting]);

        $this->assertSame(1, $scores['pre_existing_returned_count']);
        $this->assertSame(1.0, $scores['precision_strict'], 'Production silently skips an already-recorded guideline, so it cannot be graded.');
        $this->assertSame(0, $scores['false_positives_strict']);
    }

    public function test_invalid_guideline_ids_never_reach_the_agreement_score(): void
    {
        [$accepted] = $this->guidelineIds(1);

        $case = $this->recommenderCase(['accepted_guideline_ids' => [$accepted]]);

        $scores = $this->grade($case, [$accepted, 999999]);

        $this->assertSame(1, $scores['returned_count'], 'An id production would refuse to store is not a recommendation.');
        $this->assertSame(1.0, $scores['precision_strict']);
    }

    public function test_gold_labels_override_mined_ones(): void
    {
        [$first, $second] = $this->guidelineIds(2);

        $case = $this->recommenderCase(['accepted_guideline_ids' => [$first]]);
        $case->update(['labels' => [
            'mined' => $case->labels['mined'],
            'gold' => ['accepted_guideline_ids' => [$second], 'rejected_guideline_ids' => [$first]],
        ]]);

        $scores = $this->grade($case->refresh(), [$first]);

        $this->assertSame(0.0, $scores['precision_strict'], 'The curated verdict is the one that counts.');
    }

    public function test_an_unlabelled_case_is_reported_as_unscoreable_with_drift_only(): void
    {
        [$one, $two] = $this->guidelineIds(2);

        $case = EvalCase::factory()->create([
            'dataset_version' => 'agreement-test',
            'baseline' => ['guideline_ids' => [$one, $two]],
        ]);

        $scores = $this->grade($case, [$one]);

        $this->assertFalse($scores['agreement_scored']);
        $this->assertSame('no labels', $scores['agreement_skipped']);
        $this->assertSame(0.5, $scores['baseline_jaccard'], 'Drift from the recorded run is still worth reporting.');
        $this->assertNull($scores['precision_strict']);
    }

    public function test_a_failed_result_is_not_scored(): void
    {
        [$accepted] = $this->guidelineIds(1);

        $case = $this->recommenderCase(['accepted_guideline_ids' => [$accepted]]);

        $result = EvalResult::factory()->failed()->create([
            'eval_run_id' => $this->evalRun()->id,
            'eval_case_id' => $case->id,
        ]);

        $scores = app(AgreementGrader::class)->grade($case, $result);

        $this->assertFalse($scores['agreement_scored']);
        $this->assertSame('result errored', $scores['agreement_skipped']);
    }

    public function test_it_scores_structured_output_as_a_faithful_transformation(): void
    {
        $payload = $this->payload();
        $case = $this->structuredOutputCase($payload);

        $scores = $this->gradeStructured($case, $payload);

        $this->assertTrue($scores['agreement_scored']);
        $this->assertSame(1.0, $scores['field_match_rate']);
        $this->assertSame([], $scores['field_mismatches']);
        $this->assertTrue($scores['guideline_ids_exact']);
        $this->assertTrue($scores['feedback_match']);
        $this->assertSame(0, $scores['dropped_guideline_count']);
        $this->assertSame(0, $scores['added_guideline_count']);
        $this->assertSame(0, $scores['duplicate_guideline_count']);
    }

    public function test_a_guideline_emitted_twice_is_not_an_exact_match(): void
    {
        $payload = $this->payload();
        $case = $this->structuredOutputCase($payload);
        $duplicated = $payload;
        $duplicated['guidelines'][] = $payload['guidelines'][0];

        $scores = $this->gradeStructured($case, $duplicated);

        $this->assertFalse($scores['guideline_ids_exact'], 'Keying by number must not hide a repeated guideline.');
        $this->assertSame(1, $scores['duplicate_guideline_count']);
        $this->assertSame(0, $scores['added_guideline_count']);
        $this->assertSame(1.0, $scores['field_match_rate'], 'The repeated copy is faithful field by field; the defect is the repetition.');
    }

    public function test_rewritten_and_dropped_content_is_caught_field_by_field(): void
    {
        $payload = $this->payload(2);
        $case = $this->structuredOutputCase($payload);

        $rewritten = $payload;
        $rewritten['guidelines'][0]['recommendation'] = 'Something the recommender never said.';
        $rewritten['guidelines'][0]['impact'] = 'Low';
        unset($rewritten['guidelines'][1]);
        $rewritten['guidelines'] = array_values($rewritten['guidelines']);
        $rewritten['feedback'] = 'Summarised differently.';

        $scores = $this->gradeStructured($case, $rewritten);

        $number = $payload['guidelines'][0]['number'];
        $dropped = $payload['guidelines'][1]['number'];

        // 2 guidelines × 10 schema fields; the two rewrites plus the ten fields
        // of the dropped guideline are all misses.
        $this->assertSame(20, $scores['fields_compared']);
        $this->assertSame(8, $scores['fields_matched']);
        $this->assertSame(0.4, $scores['field_match_rate']);
        $this->assertContains("$number.recommendation", $scores['field_mismatches']);
        $this->assertContains("$number.impact", $scores['field_mismatches']);
        $this->assertContains("$dropped.heading", $scores['field_mismatches']);
        $this->assertSame(1, $scores['dropped_guideline_count']);
        $this->assertFalse($scores['guideline_ids_exact']);
        $this->assertFalse($scores['feedback_match']);
    }

    public function test_whitespace_and_case_differences_are_not_treated_as_drift(): void
    {
        $payload = $this->payload();
        $case = $this->structuredOutputCase($payload);

        $reshaped = $payload;
        $reshaped['guidelines'][0]['observation'] = "  The Logo\n  has  no alt attribute.  ";
        $payload['guidelines'][0]['observation'] = 'The logo has no alt attribute.';

        $scores = $this->gradeStructured($this->structuredOutputCase($payload), $reshaped);

        $this->assertSame(1.0, $scores['field_match_rate']);
    }

    public function test_a_truncated_reference_is_skipped_rather_than_scored_against_nothing(): void
    {
        $payload = $this->payload();
        $case = $this->structuredOutputCase($payload);

        // The source run finished with `Length`, so the recorded narrative is
        // JSON cut off mid-structure.
        $case->update(['input' => [
            'schema' => 'recommended_guidelines',
            'data' => substr(json_encode($payload), 0, 120),
        ]]);

        $scores = $this->gradeStructured($case->refresh(), $payload);

        $this->assertFalse($scores['agreement_scored']);
        $this->assertSame('reference not parseable', $scores['agreement_skipped']);
        $this->assertSame('regex', $scores['reference_parse_method']);
        $this->assertNull($scores['added_guideline_count'], 'A faithful answer must not be reported as invention.');
    }

    public function test_re_grading_clears_a_stale_score_when_a_case_stops_being_scoreable(): void
    {
        $payload = $this->payload();
        $case = $this->structuredOutputCase($payload);
        $result = $this->gradeStructured($case, $payload) === [] ? null : EvalResult::whereJsonContains('output->structured->feedback', $payload['feedback'])->first();

        // Grade once while the reference is usable, persisting a real score...
        $usable = EvalResult::factory()->create([
            'eval_run_id' => $this->evalRun()->id,
            'eval_case_id' => $case->id,
            'output' => ['text' => json_encode($payload), 'structured' => $payload],
        ]);
        $usable->mergeScores(app(AgreementGrader::class)->grade($case, $usable));
        $this->assertSame(0, $usable->refresh()->score('added_guideline_count'));

        // ...then the source narrative turns out to be truncated and the case
        // is re-graded: the stale real score must not survive the merge.
        $case->update(['input' => [
            'schema' => 'recommended_guidelines',
            'data' => substr(json_encode($payload), 0, 120),
        ]]);
        $usable->mergeScores(app(AgreementGrader::class)->grade($case->refresh(), $usable));

        $this->assertNull($usable->refresh()->score('added_guideline_count'), 'A retracted score must be nulled out, not left from the previous grade.');
    }

    public function test_a_reference_with_no_guidelines_is_still_a_reference(): void
    {
        $case = $this->structuredOutputCase(['guidelines' => [], 'feedback' => 'No barriers found.']);

        $scores = $this->gradeStructured($case, ['guidelines' => [], 'feedback' => 'No barriers found.']);

        $this->assertTrue($scores['agreement_scored'], 'A narrative that genuinely found nothing is decodable and gradeable.');
        $this->assertNull($scores['field_match_rate']);
        $this->assertTrue($scores['feedback_match']);
    }

    /**
     * @param  array<int, int>  $returnedIds
     * @return array<string, mixed>
     */
    private function grade(EvalCase $case, array $returnedIds): array
    {
        $result = $this->resultReturning($this->evalRun(), $case, $returnedIds);

        return app(AgreementGrader::class)->grade($case, $result);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function gradeStructured(EvalCase $case, array $payload): array
    {
        $result = EvalResult::factory()->create([
            'eval_run_id' => $this->evalRun()->id,
            'eval_case_id' => $case->id,
            'output' => ['text' => json_encode($payload), 'structured' => $payload],
        ]);

        return app(AgreementGrader::class)->grade($case, $result);
    }

    /**
     * @param  array<int, int>  $returnedIds
     */
    private function resultReturning(EvalRun $run, EvalCase $case, array $returnedIds): EvalResult
    {
        $payload = [
            'guidelines' => array_map(fn (int $id) => ['number' => $id, 'heading' => "Guideline $id"], $returnedIds),
            'feedback' => 'Replayed.',
        ];

        return EvalResult::factory()->create([
            'eval_run_id' => $run->id,
            'eval_case_id' => $case->id,
            'output' => ['text' => json_encode($payload), 'structured' => $payload],
        ]);
    }

    /**
     * @param  array<string, array<int, int>>  $labels
     */
    private function recommenderCase(array $labels): EvalCase
    {
        return EvalCase::factory()->create([
            'dataset_version' => 'agreement-test',
            'labels' => ['mined' => $labels],
            'baseline' => ['guideline_ids' => $labels['accepted_guideline_ids'] ?? []],
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function structuredOutputCase(array $payload): EvalCase
    {
        return EvalCase::factory()->structuredOutput()->create([
            'dataset_version' => 'agreement-test',
            'input' => ['schema' => 'recommended_guidelines', 'data' => json_encode($payload)],
        ]);
    }

    private function evalRun(): EvalRun
    {
        return EvalRun::factory()->create(['dataset_version' => 'agreement-test']);
    }

    /**
     * Guideline ids have to exist: the grader only counts ids production would
     * accept, exactly as `ScopeAnalyzer` does before storing an issue.
     *
     * @return array<int, int>
     */
    private function guidelineIds(int $count): array
    {
        return array_map(fn () => $this->makeGuideline()->id, range(1, $count));
    }

    /**
     * A recommended-guidelines payload with every schema field populated.
     *
     * @return array<string, mixed>
     */
    private function payload(int $guidelines = 1): array
    {
        return [
            'guidelines' => array_map(fn (int $id) => [
                'target' => 'Main navigation',
                'reasoning' => 'The images carry meaning.',
                'number' => $id,
                'heading' => 'Images need alt text',
                'criteria' => '1.1.1 Non-text Content',
                'assessment' => 'Fail',
                'observation' => 'The logo has no alt attribute.',
                'recommendation' => 'Add descriptive alt text.',
                'testing' => 'Inspect the img element.',
                'impact' => 'Serious',
            ], $this->guidelineIds($guidelines)),
            'feedback' => 'One issue found.',
        ];
    }
}
