<?php

namespace App\Services\Evals;

use App\Enums\Agents;
use App\Enums\MetricDirection;
use App\Models\EvalCase;
use App\Models\EvalRun;
use Illuminate\Support\Collection;

/**
 * Turns two runs into a verdict.
 *
 * The comparison is strictly pairwise: only cases both runs answered are used,
 * and each case contributes one number per model, averaged first across repeats.
 * Averaging repeats before pairing is deliberate — repeats measure how much one
 * model varies when asked twice, which is noise to be smoothed here and reported
 * separately, not extra evidence about which model is better.
 *
 * A verdict is only ever issued against margins recorded on the candidate run
 * *before* it executed. That ordering is the whole point: a threshold chosen
 * after seeing the numbers is not a test, it is a rationalisation.
 */
class ReportBuilder
{
    public const int DEFAULT_WORST_CASES = 5;

    public function __construct(
        private Statistics $statistics,
        private MetricCatalog $catalog,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(EvalRun $candidate, EvalRun $baseline, int $worstCases = self::DEFAULT_WORST_CASES): array
    {
        $candidateScores = $this->scoresByCase($candidate);
        $baselineScores = $this->scoresByCase($baseline);

        $pairedCaseIds = array_values(array_intersect(
            array_keys($candidateScores),
            array_keys($baselineScores)
        ));

        // A metric a margin was registered for is compared even when neither run
        // scored it: otherwise an unlabelled dataset would drop its quality
        // metric from the table and pass on reliability alone.
        $metricNames = $this->catalog->order(array_unique([
            ...$this->comparableMetrics($candidateScores, $baselineScores, $pairedCaseIds),
            ...array_keys((array) $candidate->margins),
        ]));

        $metrics = [];

        foreach ($metricNames as $metric) {
            $metrics[$metric] = $this->compareMetric(
                $metric,
                $candidate,
                $candidateScores,
                $baselineScores,
                $pairedCaseIds
            );
        }

        return [
            'candidate' => $this->describeRun($candidate),
            'baseline' => $this->describeRun($baseline),
            'warnings' => $this->warnings($candidate, $baseline, $pairedCaseIds),
            'paired_cases' => count($pairedCaseIds),
            'candidate_only_cases' => count(array_diff(array_keys($candidateScores), $pairedCaseIds)),
            'baseline_only_cases' => count(array_diff(array_keys($baselineScores), $pairedCaseIds)),
            'metrics' => $metrics,
            'primary_metric' => $primary = $this->primaryMetric($metrics),
            'worst_cases' => $primary === null
                ? []
                : $this->worstCases($primary, $candidateScores, $baselineScores, $pairedCaseIds, $worstCases),
            'verdict' => $this->overallVerdict($metrics),
        ];
    }

    /**
     * The report as markdown, for pasting into a ticket or committing beside a
     * decision. Warnings come first: a reader who skims should hit the reasons
     * not to trust the numbers before hitting the numbers.
     *
     * @param  array<string, mixed>  $report
     */
    public function toMarkdown(array $report): string
    {
        $candidate = $report['candidate'];
        $baseline = $report['baseline'];

        $lines = [
            '# Model comparison',
            '',
            sprintf('**Verdict: %s**', strtoupper((string) $report['verdict'])),
            '',
            sprintf('- Candidate: run %d — %s', $candidate['id'], $candidate['label']),
            sprintf('  - models: %s', $this->describeModels($candidate)),
            sprintf('- Baseline: run %d — %s', $baseline['id'], $baseline['label']),
            sprintf('  - models: %s', $this->describeModels($baseline)),
            sprintf('- Dataset %s · %d paired cases · %d repeats', $candidate['dataset_version'], $report['paired_cases'], $candidate['repeats']),
            sprintf('- Errors: candidate %d/%d, baseline %d/%d', $candidate['errors'], $candidate['results'], $baseline['errors'], $baseline['results']),
        ];

        if ($report['warnings'] !== []) {
            $lines[] = '';
            $lines[] = '## Read this first';
            $lines[] = '';

            foreach ($report['warnings'] as $warning) {
                $lines[] = "- $warning";
            }
        }

        $lines = [...$lines, '', '## Metrics', '', ...$this->markdownMetricTable($report['metrics'])];

        $lines[] = '';
        $lines[] = 'A difference is only worth acting on when the confidence interval excludes zero. '
            .'`repeat sd` is how much the same model varied across repeats of the same case — a difference '
            .'smaller than that is noise. Every other score a grader recorded is still on the result rows.';

        if ($report['worst_cases'] !== []) {
            $lines = [...$lines, '', '## Where the candidate lost most ground', '', ...$this->markdownWorstCases($report['worst_cases'])];
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, array<string, mixed>>  $metrics
     * @return array<int, string>
     */
    private function markdownMetricTable(array $metrics): array
    {
        $rows = [
            '| metric | baseline | candidate | diff | 95% CI | repeat sd | margin | verdict |',
            '| --- | --- | --- | --- | --- | --- | --- | --- |',
        ];

        foreach ($metrics as $metric => $row) {
            $rows[] = sprintf(
                '| %s | %s | %s | %s | %s | %s | %s | %s |',
                $metric,
                $this->number($row['baseline_mean']),
                $this->number($row['candidate_mean']),
                $this->number($row['difference']),
                $row['ci_low'] === null ? '—' : sprintf('%s to %s', $this->number($row['ci_low']), $this->number($row['ci_high'])),
                $this->number($row['candidate_repeat_sd']),
                $row['margin'] === null ? '—' : $this->number($row['margin']),
                $row['verdict'],
            );
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $cases
     * @return array<int, string>
     */
    private function markdownWorstCases(array $cases): array
    {
        $rows = [
            sprintf('Ranked by `%s`.', $cases[0]['metric']),
            '',
            '| case | agent | baseline | candidate | diff | source run |',
            '| --- | --- | --- | --- | --- | --- |',
        ];

        foreach ($cases as $case) {
            $rows[] = sprintf(
                '| %d | %s | %s | %s | %s | %s |',
                $case['eval_case_id'],
                $case['agent'],
                $this->number($case['baseline']),
                $this->number($case['candidate']),
                $this->number($case['difference']),
                $case['source_chat_history_ulid'] ?? '—',
            );
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $run
     */
    private function describeModels(array $run): string
    {
        $models = collect($run['models'])->map(function ($model, $profile) use ($run) {
            $effort = $run['reasoning_effort'][$profile] ?? null;

            return $effort ? "$profile: $model (effort: $effort)" : "$profile: $model";
        })->implode(', ');

        return $models === '' ? 'unknown' : $models;
    }

    private function number(mixed $value): string
    {
        if ($value === null) {
            return '—';
        }

        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.') ?: '0';
    }

    /**
     * One averaged score set per case, plus the within-case spread across repeats.
     *
     * @return array<int, array{values: array<string, float>, spread: array<string, float>, repeats: int}>
     */
    private function scoresByCase(EvalRun $run): array
    {
        $byCase = $run->results()->orderBy('eval_case_id')->get()->groupBy('eval_case_id');

        $out = [];

        foreach ($byCase as $caseId => $results) {
            $collected = [];

            foreach ($results as $result) {
                foreach (($result->scores ?? []) as $metric => $value) {
                    if ($this->catalog->isComparable($metric, $value)) {
                        $collected[$metric][] = is_bool($value) ? (int) $value : (float) $value;
                    }
                }
            }

            $values = [];
            $spread = [];

            foreach ($collected as $metric => $samples) {
                $values[$metric] = $this->statistics->mean($samples);
                $spread[$metric] = $this->statistics->standardDeviation($samples);
            }

            $out[(int) $caseId] = [
                'values' => $values,
                'spread' => $spread,
                'repeats' => $results->count(),
            ];
        }

        return $out;
    }

    /**
     * Catalogued metrics present on at least one paired case in both runs.
     *
     * @param  array<int, array<string, mixed>>  $candidateScores
     * @param  array<int, array<string, mixed>>  $baselineScores
     * @param  array<int, int>  $pairedCaseIds
     * @return array<int, string>
     */
    private function comparableMetrics(array $candidateScores, array $baselineScores, array $pairedCaseIds): array
    {
        $shared = [];

        foreach ($pairedCaseIds as $caseId) {
            $shared = [...$shared, ...array_keys(array_intersect_key(
                $candidateScores[$caseId]['values'],
                $baselineScores[$caseId]['values']
            ))];
        }

        return $this->catalog->order(array_values(array_unique($shared)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $candidateScores
     * @param  array<int, array<string, mixed>>  $baselineScores
     * @param  array<int, int>  $pairedCaseIds
     * @return array<string, mixed>
     */
    private function compareMetric(
        string $metric,
        EvalRun $candidate,
        array $candidateScores,
        array $baselineScores,
        array $pairedCaseIds,
    ): array {
        $candidateValues = [];
        $baselineValues = [];
        $differences = [];
        $candidateSpread = [];
        $baselineSpread = [];

        foreach ($pairedCaseIds as $caseId) {
            $left = $candidateScores[$caseId]['values'][$metric] ?? null;
            $right = $baselineScores[$caseId]['values'][$metric] ?? null;

            // A case only contributes where both models produced the metric. A
            // score missing on one side usually means that case was unscoreable
            // there, and inventing a zero would fabricate a difference.
            if ($left === null || $right === null) {
                continue;
            }

            $candidateValues[] = $left;
            $baselineValues[] = $right;
            $differences[] = $left - $right;
            $candidateSpread[] = $candidateScores[$caseId]['spread'][$metric] ?? 0.0;
            $baselineSpread[] = $baselineScores[$caseId]['spread'][$metric] ?? 0.0;
        }

        $direction = $this->catalog->directionOf($metric);
        $bootstrap = $this->statistics->pairedBootstrap($differences);

        return [
            'metric' => $metric,
            'direction' => $direction->value,
            'n' => count($differences),
            'baseline_mean' => $differences === [] ? null : round($this->statistics->mean($baselineValues), 6),
            'candidate_mean' => $differences === [] ? null : round($this->statistics->mean($candidateValues), 6),
            'difference' => $bootstrap['mean'],
            'ci_low' => $bootstrap['ci_low'],
            'ci_high' => $bootstrap['ci_high'],
            'excludes_zero' => $bootstrap['excludes_zero'],
            'baseline_repeat_sd' => $baselineSpread === [] ? null : round($this->statistics->mean($baselineSpread), 6),
            'candidate_repeat_sd' => $candidateSpread === [] ? null : round($this->statistics->mean($candidateSpread), 6),
            ...$this->verdictFor($metric, $direction, $bootstrap, $candidate),
        ];
    }

    /**
     * Non-inferiority against a pre-registered margin.
     *
     * The question is never "is the candidate better" but "can we rule out its
     * being worse by more than we agreed to tolerate". So the test is on the
     * near edge of the confidence interval: for a metric we want high, the lower
     * bound must stay above `-margin`.
     *
     * Missing that is not by itself a failure. An interval that still reaches
     * zero has not shown the candidate to be worse at all, only that the cases
     * were too few to rule it out, so it is inconclusive. It fails only once
     * the whole interval sits on the losing side of zero.
     *
     * @param  array{ci_low: ?float, ci_high: ?float}  $bootstrap
     * @return array{margin: ?float, verdict: string}
     */
    private function verdictFor(string $metric, MetricDirection $direction, array $bootstrap, EvalRun $candidate): array
    {
        $margin = data_get($candidate->margins, "$metric.margin", data_get($candidate->margins, $metric));

        if (! is_numeric($margin) || $direction === MetricDirection::Neutral) {
            return ['margin' => null, 'verdict' => 'not assessed'];
        }

        $margin = abs((float) $margin);

        if ($bootstrap['ci_low'] === null) {
            return ['margin' => $margin, 'verdict' => 'insufficient data'];
        }

        // Oriented so a loss is always negative, whichever way the metric points.
        [$nearEdge, $farEdge] = $direction === MetricDirection::HigherBetter
            ? [$bootstrap['ci_low'], $bootstrap['ci_high']]
            : [-$bootstrap['ci_high'], -$bootstrap['ci_low']];

        $verdict = match (true) {
            $nearEdge >= -$margin => 'pass',
            $farEdge < 0 => 'fail',
            default => 'inconclusive',
        };

        return ['margin' => $margin, 'verdict' => $verdict];
    }

    /**
     * @param  array<string, array<string, mixed>>  $metrics
     */
    private function overallVerdict(array $metrics): string
    {
        $assessed = array_filter($metrics, fn (array $row) => $row['verdict'] !== 'not assessed');

        if ($assessed === []) {
            return 'no margins registered';
        }

        foreach ($assessed as $row) {
            if ($row['verdict'] === 'fail') {
                return 'fail';
            }
        }

        return array_intersect(['insufficient data', 'inconclusive'], array_column($assessed, 'verdict')) === []
            ? 'pass'
            : 'inconclusive';
    }

    /**
     * The metric the per-case diff table is ranked by: whichever of the two
     * quality metrics this pair of runs actually produced.
     *
     * @param  array<string, array<string, mixed>>  $metrics
     */
    private function primaryMetric(array $metrics): ?string
    {
        foreach (['f1_strict', 'field_match_rate', 'schema_valid', 'completed'] as $candidate) {
            if (($metrics[$candidate]['n'] ?? 0) > 0) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * The cases where the candidate lost most ground, so a regression can be
     * read rather than inferred from an average.
     *
     * @param  array<int, array<string, mixed>>  $candidateScores
     * @param  array<int, array<string, mixed>>  $baselineScores
     * @param  array<int, int>  $pairedCaseIds
     * @return array<int, array<string, mixed>>
     */
    private function worstCases(
        string $metric,
        array $candidateScores,
        array $baselineScores,
        array $pairedCaseIds,
        int $limit,
    ): array {
        $sign = $this->catalog->directionOf($metric)->improvementSign();

        $rows = collect($pairedCaseIds)
            ->map(function (int $caseId) use ($metric, $candidateScores, $baselineScores) {
                $left = $candidateScores[$caseId]['values'][$metric] ?? null;
                $right = $baselineScores[$caseId]['values'][$metric] ?? null;

                return $left === null || $right === null ? null : [
                    'eval_case_id' => $caseId,
                    'baseline' => round($right, 4),
                    'candidate' => round($left, 4),
                    'difference' => round($left - $right, 4),
                ];
            })
            ->filter()
            ->filter(fn (array $row) => $row['difference'] * $sign < 0)
            ->sortBy(fn (array $row) => $row['difference'] * $sign)
            ->take($limit)
            ->values();

        return $this->withCaseContext($rows, $metric);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function withCaseContext(Collection $rows, string $metric): array
    {
        $cases = EvalCase::with('agent')
            ->whereIn('id', $rows->pluck('eval_case_id'))
            ->get()
            ->keyBy('id');

        return $rows->map(fn (array $row) => [
            ...$row,
            'metric' => $metric,
            'agent' => $cases[$row['eval_case_id']]?->agent?->name ?? 'unknown',
            'source_chat_history_ulid' => $cases[$row['eval_case_id']]?->source_chat_history_ulid,
        ])->all();
    }

    /**
     * @param  array<int, int>  $pairedCaseIds
     * @return array<int, string>
     */
    private function warnings(EvalRun $candidate, EvalRun $baseline, array $pairedCaseIds): array
    {
        $warnings = [];

        if ($candidate->dataset_version !== $baseline->dataset_version) {
            $warnings[] = sprintf(
                'Datasets differ (%s vs %s). The cases are not the same questions, so these runs are not comparable.',
                $candidate->dataset_version,
                $baseline->dataset_version,
            );
        }

        if ($candidate->getKey() === $baseline->getKey()) {
            $warnings[] = 'Candidate and baseline are the same run.';
        }

        if ($pairedCaseIds === []) {
            $warnings[] = 'No case was answered by both runs, so there is nothing to compare.';
        }

        if ($candidate->models === $baseline->models) {
            $warnings[] = $candidate->git_sha && $baseline->git_sha && $candidate->git_sha !== $baseline->git_sha
                ? sprintf(
                    'Both runs used the same models, so a difference here comes from the code or prompts at %s versus %s, or from sampling noise.',
                    $candidate->git_sha,
                    $baseline->git_sha,
                )
                : 'Both runs used the same models; any difference here is sampling noise.';
        }

        if (($candidate->repeats ?? 1) < 2) {
            $warnings[] = 'Single repeat: within-model variance is unmeasured, so a small difference cannot be distinguished from noise.';
        }

        if ($candidate->margins === null) {
            $warnings[] = 'No margins were registered on the candidate run, so no pass/fail verdict can be issued.';
        }

        $uncachedRuns = array_filter([$baseline, $candidate], fn (EvalRun $run) => $this->predatesCachedTokenCounting($run));

        if ($uncachedRuns !== []) {
            $warnings[] = sprintf(
                '`prompt_tokens` on run %s left out cached input, so it understates any model that reused a cached prompt. Re-run to compare token counts.',
                implode(' and ', array_map(fn (EvalRun $run) => $run->getKey(), $uncachedRuns)),
            );
        }

        return $warnings;
    }

    /**
     * StructuredOutput runs recorded before `EvalRunner` counted cached input
     * carry no `cachedPromptTokens`, and their stored prompt counts cannot be
     * repaired. Recommender runs are unaffected: they stream, and Prism's
     * stream handler always counted cached input.
     */
    private function predatesCachedTokenCounting(EvalRun $run): bool
    {
        return $run->results()
            ->whereNotNull('usage')
            ->whereHas('evalCase.agent', fn ($query) => $query->where('name', Agents::StructuredOutput->value))
            ->get(['usage'])
            ->contains(fn ($result) => is_array($result->usage) && ! array_key_exists('cachedPromptTokens', $result->usage));
    }

    /**
     * @return array<string, mixed>
     */
    private function describeRun(EvalRun $run): array
    {
        return [
            'id' => $run->getKey(),
            'label' => $run->label,
            'dataset_version' => $run->dataset_version,
            'status' => $run->status?->value,
            'repeats' => $run->repeats,
            'models' => data_get($run->models, 'profiles', []),
            'provider' => data_get($run->models, 'provider'),
            'reasoning_effort' => data_get($run->models, 'reasoning_effort', []),
            'git_sha' => $run->git_sha,
            'results' => $run->results()->count(),
            'errors' => $run->results()->whereNotNull('error')->count(),
        ];
    }
}
