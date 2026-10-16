<?php

namespace App\Services\Evals\Graders;

use App\Enums\Agents;
use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Services\Evals\RecommendationParser;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;

/**
 * Scores a replayed result against a human verdict rather than against the old
 * model's opinion.
 *
 * For the recommender that verdict is `issues.ai_status`, mined per historical
 * run and optionally overridden by a curated gold label. For StructuredOutput
 * the task is a faithful transformation, so the case's own input is the
 * reference: any field that changes on the way through is a defect.
 *
 * Nothing here compares a run numerically against the recorded history. The one
 * historical figure reported, `baseline_jaccard`, measures *drift* — prompts,
 * Prism and the guidelines corpus have all moved — and is never a quality score.
 */
class AgreementGrader
{
    /**
     * Keys a scored recommender case can carry, beyond `agreement_scored` and
     * the always-present `returned_count`/`baseline_jaccard`.
     *
     * `EvalResult::mergeScores()` merges additively rather than replacing, so a
     * case that stops being scoreable (a label was corrected, a reference turned
     * out to be truncated) would otherwise leave its last real scores behind
     * forever. Every abstaining return nulls these out explicitly so a re-grade
     * overwrites rather than accumulates.
     *
     * @var array<int, string>
     */
    private const array RECOMMENDER_SCORE_KEYS = [
        'scoreable_count', 'unlabelled_returned_count', 'untriaged_returned_count', 'pre_existing_returned_count',
        'precision_strict', 'recall_strict', 'f1_strict', 'true_positives_strict', 'false_positives_strict', 'false_negatives_strict',
        'precision_lenient', 'recall_lenient', 'f1_lenient', 'true_positives_lenient', 'false_positives_lenient', 'false_negatives_lenient',
    ];

    /**
     * @var array<int, string>
     */
    private const array STRUCTURED_OUTPUT_SCORE_KEYS = [
        'fields_compared', 'fields_matched', 'field_match_rate', 'field_mismatches',
        'guideline_ids_exact', 'dropped_guideline_count', 'added_guideline_count', 'duplicate_guideline_count', 'feedback_match',
        'reference_truncated', 'out_of_schema_guideline_count',
    ];

    /**
     * @return array<string, mixed>
     */
    public function grade(EvalCase $case, EvalResult $result): array
    {
        return match ($this->agentOf($case)) {
            Agents::GuidelineRecommender => $this->gradeRecommender($case, $result),
            Agents::StructuredOutput => $this->gradeStructuredOutput($case, $result),
            default => ['agreement_scored' => false, 'agreement_skipped' => 'unsupported agent'],
        };
    }

    /**
     * Precision and recall of the returned guideline-ID set.
     *
     * Two variants are always reported side by side because `modified` is
     * ambiguous: it covers both "right guideline, reworded" and "half wrong".
     * Strict counts only `accepted` as a hit; lenient also counts `modified`.
     *
     * @return array<string, mixed>
     */
    private function gradeRecommender(EvalCase $case, EvalResult $result): array
    {
        $labels = $case->effectiveLabels();
        $returned = $this->returnedGuidelineIds($result);
        $baselineJaccard = $this->jaccard($returned, $this->ids($case->baseline['guideline_ids'] ?? []));

        if ($result->failed() || $labels === []) {
            return [
                ...array_fill_keys(self::RECOMMENDER_SCORE_KEYS, null),
                'agreement_scored' => false,
                'agreement_skipped' => $result->failed() ? 'result errored' : 'no labels',
                'returned_count' => count($returned),
                'baseline_jaccard' => $baselineJaccard,
            ];
        }

        $accepted = $this->ids($labels['accepted_guideline_ids'] ?? []);
        $modified = $this->ids($labels['modified_guideline_ids'] ?? []);
        $rejected = $this->ids($labels['rejected_guideline_ids'] ?? []);
        $untriaged = $this->ids($labels['untriaged_guideline_ids'] ?? []);
        $missed = $this->ids($labels['missed_guideline_ids'] ?? []);
        $preExisting = $this->ids($labels['pre_existing_guideline_ids'] ?? []);

        // Two kinds of returned id carry no verdict and so cannot be judged:
        // one a reviewer never triaged, and one production would have silently
        // skipped as already recorded. Both are excluded rather than guessed at.
        $judgeable = array_values(array_diff($returned, $untriaged, $preExisting));

        // An id no reviewer ever saw is not evidence either way — the historical
        // model simply never offered it. Counting it as a false positive would
        // punish a candidate for surfacing something new.
        $labelled = array_merge($accepted, $modified, $rejected);
        $scoreable = array_values(array_intersect($judgeable, $labelled));

        $strict = $this->rates($scoreable, $accepted, array_merge($modified, $rejected), $missed);
        $lenient = $this->rates($scoreable, array_merge($accepted, $modified), $rejected, $missed);

        return [
            'agreement_scored' => true,
            'returned_count' => count($returned),
            'scoreable_count' => count($scoreable),
            'unlabelled_returned_count' => count(array_diff($judgeable, $labelled)),
            'untriaged_returned_count' => count(array_intersect($returned, $untriaged)),
            'pre_existing_returned_count' => count(array_intersect($returned, $preExisting)),

            'precision_strict' => $strict['precision'],
            'recall_strict' => $strict['recall'],
            'f1_strict' => $strict['f1'],
            'true_positives_strict' => $strict['true_positives'],
            'false_positives_strict' => $strict['false_positives'],
            'false_negatives_strict' => $strict['false_negatives'],

            'precision_lenient' => $lenient['precision'],
            'recall_lenient' => $lenient['recall'],
            'f1_lenient' => $lenient['f1'],
            'true_positives_lenient' => $lenient['true_positives'],
            'false_positives_lenient' => $lenient['false_positives'],
            'false_negatives_lenient' => $lenient['false_negatives'],

            // Drift from the recorded run, not a quality score. See the class doc.
            'baseline_jaccard' => $baselineJaccard,
        ];
    }

    /**
     * Field-level fidelity of the transformation.
     *
     * StructuredOutput exists only to reshape the recommender's text into the
     * schema, so the input is the ground truth and every schema field is
     * compared to it. Whitespace and case are normalised; anything else that
     * differs is the model rewriting content it was asked to carry through.
     *
     * @return array<string, mixed>
     */
    private function gradeStructuredOutput(EvalCase $case, EvalResult $result): array
    {
        $reference = RecommendationParser::parse($case->input['data'] ?? null);
        $payload = is_array($result->output['structured'] ?? null) ? $result->output['structured'] : null;

        if ($result->failed() || $payload === null) {
            return [
                ...array_fill_keys(self::STRUCTURED_OUTPUT_SCORE_KEYS, null),
                'agreement_scored' => false,
                'agreement_skipped' => $result->failed() ? 'result errored' : 'no structured payload',
            ];
        }

        // Prose has no structure to carry through: it is the recommender asking
        // for more of the page, so there is nothing to hold the answer to.
        if ($reference['parse_method'] !== 'json' && ! str_contains((string) ($case->input['data'] ?? ''), '"guidelines"')) {
            return [
                ...array_fill_keys(self::STRUCTURED_OUTPUT_SCORE_KEYS, null),
                'agreement_scored' => false,
                'agreement_skipped' => 'reference is prose',
                'reference_parse_method' => $reference['parse_method'],
            ];
        }

        if ($reference['parse_method'] !== 'json') {
            return $this->gradeTruncatedStructuredOutput((string) ($case->input['data'] ?? ''), $payload);
        }

        // A reference entry naming several guidelines ("33 and 34") is faithfully
        // carried through as one entry per guideline. The answer is never split:
        // a model that passes the combined number through, or keeps only its
        // first guideline, has lost one that production cannot store.
        $producedList = $this->producedGuidelines($payload);
        $referenceSplit = RecommendationParser::splitCombinedGuidelines($reference['guidelines']);
        $referenceGuidelines = array_values(array_filter($referenceSplit, fn (array $guideline) => $this->fitsSchema($guideline)));
        $expected = $this->guidelinesByNumber($referenceGuidelines);
        $produced = $this->guidelinesByNumber($producedList);

        // Keying by number collapses a guideline the model emitted twice, so
        // the exact-set check is made on the full lists, duplicates included.
        $expectedNumbers = $this->numbers($referenceGuidelines);
        $producedNumbers = $this->numbers($producedList);

        $compared = 0;
        $matched = 0;
        $mismatches = [];

        foreach ($expected as $number => $guideline) {
            foreach (array_keys(GuidelinesAnalyzerService::ISSUE_SCHEMA['properties']) as $field) {
                if (! array_key_exists($field, $guideline)) {
                    continue;
                }

                $compared++;

                if ($this->normalise($guideline[$field]) === $this->normalise($produced[$number][$field] ?? null)) {
                    $matched++;
                } else {
                    $mismatches[] = "$number.$field";
                }
            }
        }

        return [
            'agreement_scored' => true,
            'agreement_skipped' => null,
            'reference_parse_method' => $reference['parse_method'],
            'reference_truncated' => false,
            'out_of_schema_guideline_count' => count($referenceSplit) - count($referenceGuidelines),
            'fields_compared' => $compared,
            'fields_matched' => $matched,
            'field_match_rate' => $compared === 0 ? null : round($matched / $compared, 4),
            'field_mismatches' => array_slice($mismatches, 0, 40),
            'guideline_ids_exact' => $expectedNumbers === $producedNumbers,
            'dropped_guideline_count' => count(array_diff(array_keys($expected), array_keys($produced))),
            'added_guideline_count' => count(array_diff(array_keys($produced), array_keys($expected))),
            'duplicate_guideline_count' => count($producedNumbers) - count(array_unique($producedNumbers)),
            'feedback_match' => $this->normalise($reference['feedback']) === $this->normalise($payload['feedback'] ?? null),
        ];
    }

    /**
     * Guideline-number fidelity against a reference cut off mid-JSON.
     *
     * These are source runs that finished with `Length`. Their fields cannot be
     * recovered, but every `"number"` before the cut can, and those numbers are
     * enough to catch a model that invents a guideline to fill the gap. The last
     * guideline is usually incomplete, so dropping it counts as faithful; any
     * other drop, addition or repeat does not. The cut also falls before
     * `feedback`, so there is no summary to compare.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function gradeTruncatedStructuredOutput(string $data, array $payload): array
    {
        $scraped = RecommendationParser::scrapeGuidelines($data);
        $inSchema = array_filter($scraped, fn (array $guideline) => $this->fitsSchema($guideline));
        $expected = $this->numbers(RecommendationParser::splitCombinedGuidelines($inSchema));

        $last = array_key_last($scraped);
        $withoutPartial = $this->numbers(RecommendationParser::splitCombinedGuidelines(array_diff_key($inSchema, [$last => true])));

        $produced = $this->numbers($this->producedGuidelines($payload));
        $producedUnique = array_values(array_unique($produced));

        return [
            ...array_fill_keys(self::STRUCTURED_OUTPUT_SCORE_KEYS, null),
            'agreement_scored' => true,
            'agreement_skipped' => null,
            'reference_parse_method' => 'regex',
            'reference_truncated' => true,
            'out_of_schema_guideline_count' => count($scraped) - count($inSchema),
            'guideline_ids_exact' => $produced === $expected || $produced === $withoutPartial,
            'dropped_guideline_count' => count(array_diff(array_unique($withoutPartial), $producedUnique)),
            'added_guideline_count' => count(array_diff($producedUnique, $expected)),
            'duplicate_guideline_count' => count($produced) - count($producedUnique),
        ];
    }

    /**
     * The guidelines a structured payload returned, exactly as returned.
     *
     * @param  array<string, mixed>  $payload
     * @return array<int, mixed>
     */
    private function producedGuidelines(array $payload): array
    {
        return is_array($payload['guidelines'] ?? null) ? $payload['guidelines'] : [];
    }

    /**
     * Whether production's schema can hold a reference guideline at all. The
     * recommender sometimes lists guidelines it judged `Pass`, but the schema
     * only takes `Fail` or `Warn`, so a model that leaves them out is right to.
     *
     * @param  array<string, mixed>  $guideline
     */
    private function fitsSchema(array $guideline): bool
    {
        $assessment = $guideline['assessment'] ?? null;

        return ! is_string($assessment)
            || in_array($assessment, GuidelinesAnalyzerService::ISSUE_SCHEMA['properties']['assessment']['enum'], true);
    }

    /**
     * Every numeric guideline number in a list, sorted, duplicates kept.
     *
     * @param  array<int, mixed>  $guidelines
     * @return array<int, int>
     */
    private function numbers(array $guidelines): array
    {
        $numbers = [];

        foreach ($guidelines as $guideline) {
            $number = ((array) $guideline)['number'] ?? null;

            if (is_numeric($number)) {
                $numbers[] = (int) $number;
            }
        }

        sort($numbers);

        return $numbers;
    }

    /**
     * Precision over the ids a reviewer actually judged, recall over the ids a
     * reviewer endorsed — including the guidelines they added themselves, which
     * the model should have surfaced.
     *
     * @param  array<int, int>  $scoreable
     * @param  array<int, int>  $positives
     * @param  array<int, int>  $negatives
     * @param  array<int, int>  $missed
     * @return array{precision: ?float, recall: ?float, f1: ?float, true_positives: int, false_positives: int, false_negatives: int}
     */
    private function rates(array $scoreable, array $positives, array $negatives, array $missed): array
    {
        $truePositives = count(array_intersect($scoreable, $positives));
        $falsePositives = count(array_intersect($scoreable, $negatives));

        // A miss is a false negative by construction: it is a guideline a human
        // recorded that the model did not return.
        $expected = array_values(array_unique(array_merge($positives, $missed)));
        $falseNegatives = count(array_diff($expected, $scoreable));

        $precision = $truePositives + $falsePositives === 0
            ? null
            : round($truePositives / ($truePositives + $falsePositives), 4);

        $recall = $expected === []
            ? null
            : round(count(array_intersect($expected, $scoreable)) / count($expected), 4);

        return [
            'precision' => $precision,
            'recall' => $recall,
            'f1' => $this->f1($precision, $recall),
            'true_positives' => $truePositives,
            'false_positives' => $falsePositives,
            'false_negatives' => $falseNegatives,
        ];
    }

    private function f1(?float $precision, ?float $recall): ?float
    {
        // Precision is undefined when nothing judgeable came back, and recall is
        // then zero by construction. An answer that finds none of what reviewers
        // endorsed is the worst F1, not a case with nothing to score — otherwise
        // a model that returns nothing is dropped from the comparison instead of
        // losing it.
        if ($precision === null && $recall === 0.0) {
            return 0.0;
        }

        if ($precision === null || $recall === null || $precision + $recall === 0.0) {
            return $precision === null || $recall === null ? null : 0.0;
        }

        return round(2 * $precision * $recall / ($precision + $recall), 4);
    }

    /**
     * The ids production would have stored, so grading sees what a reviewer
     * would have seen: invalid ids never reach an issue row.
     *
     * @return array<int, int>
     */
    private function returnedGuidelineIds(EvalResult $result): array
    {
        $payload = is_array($result->output['structured'] ?? null) ? $result->output['structured'] : null;
        $guidelines = is_array($payload['guidelines'] ?? null) ? $payload['guidelines'] : [];

        return array_values(array_filter(
            RecommendationParser::fromGuidelines($guidelines)['guideline_ids'],
            fn (int $id) => GuidelinesAnalyzerService::isValidGuidelineId($id)
        ));
    }

    /**
     * @param  array<int, mixed>  $guidelines
     * @return array<int, array<string, mixed>>
     */
    private function guidelinesByNumber(array $guidelines): array
    {
        $keyed = [];

        foreach ($guidelines as $guideline) {
            $guideline = (array) $guideline;
            $number = $guideline['number'] ?? null;

            if (is_numeric($number)) {
                $keyed[(int) $number] = $guideline;
            }
        }

        ksort($keyed);

        return $keyed;
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function ids(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', array_filter($ids, 'is_numeric'))));
    }

    /**
     * @param  array<int, int>  $left
     * @param  array<int, int>  $right
     */
    private function jaccard(array $left, array $right): ?float
    {
        $union = count(array_unique(array_merge($left, $right)));

        if ($union === 0) {
            return null;
        }

        return round(count(array_intersect($left, $right)) / $union, 4);
    }

    private function normalise(mixed $value): ?string
    {
        if ($value === null || is_array($value) || is_object($value)) {
            return $value === null ? null : json_encode($value);
        }

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $value)));
    }

    private function agentOf(EvalCase $case): ?Agents
    {
        return Agents::tryFrom($case->agent?->name ?? '');
    }
}
