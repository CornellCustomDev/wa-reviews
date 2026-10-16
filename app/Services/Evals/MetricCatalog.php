<?php

namespace App\Services\Evals;

use App\Enums\MetricDirection;

/**
 * The scores a report puts in front of a reader, in reading order, and which
 * way each one points.
 *
 * Graders record many more scores than this on each result — counts, lists,
 * drift from the recorded history — and all of them stay on the result for
 * anyone digging into a case. The report tabulates only these, because a
 * forty-row table buries the three numbers a decision turns on.
 */
class MetricCatalog
{
    /**
     * @var array<string, MetricDirection>
     */
    private const array METRICS = [
        // Reliability: did a usable answer come back at all.
        'completed' => MetricDirection::HigherBetter,
        'finished_stop' => MetricDirection::HigherBetter,
        'schema_valid' => MetricDirection::HigherBetter,

        // StructuredOutput fidelity: the input is the reference, so anything
        // that changed on the way through is the model rewriting content.
        'field_match_rate' => MetricDirection::HigherBetter,
        'feedback_match' => MetricDirection::HigherBetter,
        'guideline_ids_exact' => MetricDirection::HigherBetter,

        // Recommender agreement with the reviewers' verdicts.
        'f1_strict' => MetricDirection::HigherBetter,
        'f1_lenient' => MetricDirection::HigherBetter,
        'precision_strict' => MetricDirection::HigherBetter,
        'recall_strict' => MetricDirection::HigherBetter,
        'precision_lenient' => MetricDirection::HigherBetter,
        'recall_lenient' => MetricDirection::HigherBetter,

        // Recommender output production cannot store: an entry such as
        // "33 and 34" is not a valid guideline id.
        'combined_number_count' => MetricDirection::LowerBetter,

        // Cost and speed.
        'latency_ms' => MetricDirection::LowerBetter,
        'prompt_tokens' => MetricDirection::LowerBetter,
        'completion_tokens' => MetricDirection::LowerBetter,
    ];

    public function isReported(string $metric): bool
    {
        return array_key_exists($metric, self::METRICS);
    }

    /**
     * A metric that is not reported has no direction: it is never counted for
     * or against a candidate, even if a margin names it.
     */
    public function directionOf(string $metric): MetricDirection
    {
        return self::METRICS[$metric] ?? MetricDirection::Neutral;
    }

    /**
     * Whether a recorded score can be averaged and compared. Lists and strings
     * (`schema_issues`, `field_mismatches`, `agreement_skipped`) cannot.
     */
    public function isComparable(string $metric, mixed $value): bool
    {
        return $this->isReported($metric) && (is_bool($value) || is_int($value) || is_float($value));
    }

    /**
     * The given metrics in catalog order, so two reports of the same pair of
     * runs read the same way.
     *
     * @param  array<int, string>  $metrics
     * @return array<int, string>
     */
    public function order(array $metrics): array
    {
        return array_values(array_intersect(array_keys(self::METRICS), $metrics));
    }
}
