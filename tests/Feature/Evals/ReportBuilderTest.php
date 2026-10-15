<?php

namespace Tests\Feature\Evals;

use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use App\Services\Evals\ReportBuilder;
use Tests\Feature\FeatureTestCase;

class ReportBuilderTest extends FeatureTestCase
{
    public function test_it_pairs_cases_and_reports_the_mean_difference(): void
    {
        $cases = $this->cases(4);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['f1_strict' => ['margin' => 0.02]]);

        $this->makeResults($baseline, $cases, [0.4, 0.5, 0.6, 0.5]);
        $this->makeResults($candidate, $cases, [0.5, 0.6, 0.7, 0.6]);

        $report = $this->build($candidate, $baseline);

        $this->assertSame(4, $report['paired_cases']);
        $this->assertSame(0.5, $report['metrics']['f1_strict']['baseline_mean']);
        $this->assertSame(0.6, $report['metrics']['f1_strict']['candidate_mean']);
        $this->assertSame(0.1, $report['metrics']['f1_strict']['difference']);
        $this->assertTrue($report['metrics']['f1_strict']['excludes_zero'], 'Every case improved by exactly 0.1.');
        $this->assertSame('pass', $report['verdict']);
    }

    public function test_a_case_only_one_run_answered_is_left_out(): void
    {
        $cases = $this->cases(3);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new');

        $this->makeResults($baseline, $cases, [0.5, 0.5, 0.5]);
        $this->makeResults($candidate, array_slice($cases, 0, 2), [0.5, 0.5]);

        $report = $this->build($candidate, $baseline);

        $this->assertSame(2, $report['paired_cases']);
        $this->assertSame(1, $report['baseline_only_cases']);
        $this->assertSame(2, $report['metrics']['f1_strict']['n']);
    }

    public function test_a_metric_missing_on_one_side_is_not_treated_as_zero(): void
    {
        $cases = $this->cases(2);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new');

        $this->makeResult($baseline, $cases[0], ['f1_strict' => 0.8]);
        $this->makeResult($baseline, $cases[1], ['f1_strict' => 0.8]);
        $this->makeResult($candidate, $cases[0], ['f1_strict' => 0.8]);
        // The second case was unscoreable for the candidate — no label, say.
        $this->makeResult($candidate, $cases[1], ['agreement_scored' => false]);

        $report = $this->build($candidate, $baseline);

        $this->assertSame(1, $report['metrics']['f1_strict']['n']);
        $this->assertSame(0.0, $report['metrics']['f1_strict']['difference'], 'The one comparable case is unchanged.');
    }

    public function test_repeats_are_averaged_before_pairing_and_their_spread_reported(): void
    {
        $cases = $this->cases(2);
        $baseline = $this->evalRun('old', repeats: 2);
        $candidate = $this->evalRun('new', repeats: 2);

        foreach ($cases as $case) {
            $this->makeResult($baseline, $case, ['f1_strict' => 0.5], repeat: 0);
            $this->makeResult($baseline, $case, ['f1_strict' => 0.5], repeat: 1);
            $this->makeResult($candidate, $case, ['f1_strict' => 0.4], repeat: 0);
            $this->makeResult($candidate, $case, ['f1_strict' => 0.8], repeat: 1);
        }

        $report = $this->build($candidate, $baseline);
        $metric = $report['metrics']['f1_strict'];

        $this->assertSame(2, $metric['n'], 'Two cases, not four results.');
        $this->assertSame(0.6, $metric['candidate_mean']);
        $this->assertSame(0.0, $metric['baseline_repeat_sd']);
        $this->assertSame(0.282843, $metric['candidate_repeat_sd'], 'The candidate swung between repeats; that has to be visible.');
    }

    public function test_a_regression_beyond_the_margin_fails(): void
    {
        $cases = $this->cases(5);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['f1_strict' => ['margin' => 0.02]]);

        $this->makeResults($baseline, $cases, [0.8, 0.8, 0.8, 0.8, 0.8]);
        $this->makeResults($candidate, $cases, [0.5, 0.5, 0.5, 0.5, 0.5]);

        $report = $this->build($candidate, $baseline);

        $this->assertSame('fail', $report['metrics']['f1_strict']['verdict']);
        $this->assertSame('fail', $report['verdict']);
    }

    public function test_a_regression_inside_the_margin_passes(): void
    {
        $cases = $this->cases(5);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['f1_strict' => ['margin' => 0.05]]);

        $this->makeResults($baseline, $cases, [0.8, 0.8, 0.8, 0.8, 0.8]);
        $this->makeResults($candidate, $cases, [0.79, 0.79, 0.79, 0.79, 0.79]);

        $report = $this->build($candidate, $baseline);

        $this->assertSame('pass', $report['metrics']['f1_strict']['verdict'], 'Non-inferiority allows a small agreed loss.');
    }

    public function test_a_lower_is_better_metric_is_judged_on_the_other_tail(): void
    {
        $cases = $this->cases(4);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['latency_ms' => ['margin' => 200]]);

        foreach ($cases as $case) {
            $this->makeResult($baseline, $case, ['latency_ms' => 1000]);
            $this->makeResult($candidate, $case, ['latency_ms' => 3000]);
        }

        $report = $this->build($candidate, $baseline);

        $this->assertSame('lower_better', $report['metrics']['latency_ms']['direction']);
        $this->assertSame(2000.0, $report['metrics']['latency_ms']['difference']);
        $this->assertSame('fail', $report['metrics']['latency_ms']['verdict'], 'Slower by 2s against a 200ms tolerance.');
    }

    public function test_a_score_outside_the_catalog_is_kept_off_the_report_even_with_a_margin(): void
    {
        $cases = $this->cases(3);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['baseline_jaccard' => ['margin' => 0.01]]);

        // Drift from the recorded history is not quality; it must never be
        // tabulated or given a verdict, however it is configured.
        $this->makeResults($baseline, $cases, [0.9, 0.9, 0.9], metric: 'baseline_jaccard');
        $this->makeResults($candidate, $cases, [0.2, 0.2, 0.2], metric: 'baseline_jaccard');

        $report = $this->build($candidate, $baseline);

        $this->assertArrayNotHasKey('baseline_jaccard', $report['metrics']);
        $this->assertSame('no margins registered', $report['verdict']);
    }

    public function test_a_registered_margin_whose_metric_was_never_scored_makes_the_verdict_inconclusive(): void
    {
        $cases = $this->cases(3);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: [
            'finished_stop' => ['margin' => 0.05],
            'f1_strict' => ['margin' => 0.02],
        ]);

        // Reliability is perfect on both sides, but no case carried a label, so
        // the quality metric the margins demand never produced a number.
        $this->makeResults($baseline, $cases, [1, 1, 1], metric: 'finished_stop');
        $this->makeResults($candidate, $cases, [1, 1, 1], metric: 'finished_stop');

        $report = $this->build($candidate, $baseline);

        $this->assertSame('pass', $report['metrics']['finished_stop']['verdict']);
        $this->assertSame('insufficient data', $report['metrics']['f1_strict']['verdict'], 'Absent quality evidence is not a pass.');
        $this->assertSame(0, $report['metrics']['f1_strict']['n']);
        $this->assertSame('inconclusive', $report['verdict']);
    }

    public function test_it_warns_when_the_runs_are_not_comparable(): void
    {
        $baseline = $this->evalRun('old', dataset: 'v1');
        $candidate = $this->evalRun('new', dataset: 'v2');

        $report = $this->build($candidate, $baseline);

        $this->assertStringContainsString('Datasets differ', implode(' ', $report['warnings']));
        $this->assertStringContainsString('nothing to compare', implode(' ', $report['warnings']));
    }

    public function test_it_warns_when_both_runs_used_the_same_models(): void
    {
        $models = ['provider' => 'cornell', 'profiles' => ['chat' => 'a', 'task' => 'b']];
        $cases = $this->cases(2);

        $baseline = $this->evalRun('old', models: $models);
        $candidate = $this->evalRun('new', models: $models);

        $this->makeResults($baseline, $cases, [0.5, 0.5]);
        $this->makeResults($candidate, $cases, [0.5, 0.5]);

        $report = $this->build($candidate, $baseline);

        $this->assertStringContainsString('same models', implode(' ', $report['warnings']));
    }

    public function test_it_warns_when_a_run_predates_cached_token_counting(): void
    {
        $cases = $this->cases(1);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new');

        $this->makeResult($baseline, $cases[0], ['prompt_tokens' => 41])
            ->update(['usage' => ['promptTokens' => 41, 'completionTokens' => 500]]);
        $this->makeResult($candidate, $cases[0], ['prompt_tokens' => 2857]);

        $warnings = implode(' ', $this->build($candidate, $baseline)['warnings']);

        $this->assertStringContainsString("run {$baseline->id} left out cached input", $warnings);
        $this->assertStringNotContainsString("run {$candidate->id}", $warnings);
    }

    public function test_it_lists_the_worst_per_case_regressions(): void
    {
        $cases = $this->cases(4);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new');

        $this->makeResults($baseline, $cases, [0.9, 0.9, 0.9, 0.9]);
        $this->makeResults($candidate, $cases, [0.9, 0.1, 0.5, 0.95]);

        $report = $this->build($candidate, $baseline, worst: 2);

        $this->assertSame('f1_strict', $report['primary_metric']);
        $this->assertCount(2, $report['worst_cases'], 'Only the regressions, and only as many as asked for.');
        $this->assertSame($cases[1]->id, $report['worst_cases'][0]['eval_case_id'], 'Biggest loss first.');
        $this->assertSame(-0.8, $report['worst_cases'][0]['difference']);
        $this->assertSame($cases[2]->id, $report['worst_cases'][1]['eval_case_id']);
    }

    public function test_the_markdown_report_leads_with_the_verdict_and_the_caveats(): void
    {
        $cases = $this->cases(3);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['f1_strict' => ['margin' => 0.01]]);

        $this->makeResults($baseline, $cases, [0.5, 0.5, 0.5]);
        $this->makeResults($candidate, $cases, [0.9, 0.9, 0.9]);

        $markdown = app(ReportBuilder::class)->toMarkdown($this->build($candidate, $baseline));

        $this->assertStringContainsString('**Verdict: PASS**', $markdown);
        $this->assertStringContainsString('## Read this first', $markdown);
        $this->assertStringContainsString('Single repeat', $markdown);
        $this->assertStringContainsString('| f1_strict | 0.5 | 0.9 | 0.4 |', $markdown);
    }

    public function test_the_report_command_writes_markdown_and_exits_non_zero_on_a_failure(): void
    {
        $cases = $this->cases(4);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['f1_strict' => ['margin' => 0.01]]);

        $this->makeResults($baseline, $cases, [0.9, 0.9, 0.9, 0.9]);
        $this->makeResults($candidate, $cases, [0.1, 0.1, 0.1, 0.1]);

        $path = storage_path('framework/testing/eval-report.md');

        $this->artisan('ai:eval-report', [
            '--run' => $candidate->id,
            '--baseline' => $baseline->id,
            '--out' => $path,
        ])->assertFailed();

        $this->assertStringContainsString('**Verdict: FAIL**', file_get_contents($path));

        unlink($path);
    }

    public function test_the_report_command_exits_non_zero_on_a_failure_in_json_mode_too(): void
    {
        $cases = $this->cases(4);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new', margins: ['f1_strict' => ['margin' => 0.01]]);

        $this->makeResults($baseline, $cases, [0.9, 0.9, 0.9, 0.9]);
        $this->makeResults($candidate, $cases, [0.1, 0.1, 0.1, 0.1]);

        $this->artisan('ai:eval-report', [
            '--run' => $candidate->id,
            '--baseline' => $baseline->id,
            '--json' => true,
        ])->expectsOutputToContain('"verdict": "fail"')->assertFailed();
    }

    public function test_the_report_command_can_rescore_both_runs_first(): void
    {
        $cases = $this->cases(2);
        $baseline = $this->evalRun('old');
        $candidate = $this->evalRun('new');

        // Scores recorded by an earlier grader; the stored outputs are prose,
        // which today's graders score as completed but not schema-valid.
        $this->makeResults($baseline, $cases, [0.9, 0.9]);
        $this->makeResults($candidate, $cases, [0.1, 0.1]);

        $this->artisan('ai:eval-report', [
            '--run' => $candidate->id,
            '--baseline' => $baseline->id,
            '--regrade' => true,
        ])->assertSuccessful();

        $rescored = $candidate->results()->first();

        $this->assertTrue($rescored->score('completed'));
        $this->assertFalse($rescored->score('schema_valid'));
        $this->assertNull($rescored->score('f1_strict'), 'An unlabelled case cannot keep an F1 from a previous grader.');
    }

    public function test_the_report_command_refuses_without_a_baseline(): void
    {
        $this->artisan('ai:eval-report', ['--run' => $this->evalRun('new')->id])->assertFailed();
    }

    public function test_the_report_command_defaults_the_baseline_to_the_current_baseline_run(): void
    {
        $cases = $this->cases(2);
        $dataset = 'report-baseline-default';
        $baseline = $this->evalRun('old', dataset: $dataset);
        $baseline->markAsBaseline();
        $candidate = $this->evalRun('new', dataset: $dataset);

        $this->makeResults($baseline, $cases, [0.5, 0.5]);
        $this->makeResults($candidate, $cases, [0.5, 0.5]);

        $this->artisan('ai:eval-report', ['--run' => $candidate->id])->assertSuccessful();
    }

    public function test_the_report_command_refuses_when_the_only_baseline_is_the_candidate_itself(): void
    {
        $dataset = 'report-self-baseline';
        $candidate = $this->evalRun('new', dataset: $dataset);
        $candidate->markAsBaseline();

        $this->artisan('ai:eval-report', ['--run' => $candidate->id])->assertFailed();
    }

    /**
     * @return array<string, mixed>
     */
    private function build(EvalRun $candidate, EvalRun $baseline, int $worst = 5): array
    {
        return app(ReportBuilder::class)->build($candidate, $baseline, $worst);
    }

    /**
     * @param  array<string, mixed>|null  $margins
     * @param  array<string, mixed>|null  $models
     */
    private function evalRun(string $label, ?array $margins = null, int $repeats = 1, string $dataset = 'report-test', ?array $models = null): EvalRun
    {
        return EvalRun::factory()->create([
            'label' => $label,
            'dataset_version' => $dataset,
            'repeats' => $repeats,
            'margins' => $margins,
            'models' => $models ?? ['provider' => 'cornell', 'profiles' => ['chat' => "chat-$label", 'task' => "task-$label"]],
        ]);
    }

    /**
     * @return array<int, EvalCase>
     */
    private function cases(int $count): array
    {
        return EvalCase::factory()->count($count)->create(['dataset_version' => 'report-test'])->all();
    }

    /**
     * @param  array<int, EvalCase>  $cases
     * @param  array<int, float>  $values
     */
    private function makeResults(EvalRun $run, array $cases, array $values, string $metric = 'f1_strict'): void
    {
        foreach ($cases as $index => $case) {
            $this->makeResult($run, $case, [$metric => $values[$index]]);
        }
    }

    /**
     * @param  array<string, mixed>  $scores
     */
    private function makeResult(EvalRun $run, EvalCase $case, array $scores, int $repeat = 0): EvalResult
    {
        return EvalResult::factory()->create([
            'eval_run_id' => $run->id,
            'eval_case_id' => $case->id,
            'repeat_index' => $repeat,
            'scores' => $scores,
        ]);
    }
}
