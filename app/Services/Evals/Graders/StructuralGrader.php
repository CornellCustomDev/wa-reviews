<?php

namespace App\Services\Evals\Graders;

use App\Enums\Agents;
use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Services\Evals\RecommendationParser;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;

/**
 * Free, deterministic scoring of a replayed result.
 *
 * Everything here is a property of the response alone — reliability, schema
 * conformance, cost, latency — so it can be computed without a label, without a
 * judge, and without another API call. It is deliberately the first tier: a
 * model that cannot return valid output does not need semantic grading.
 */
class StructuralGrader
{
    /** Mirrors `GuidelineRecommenderAgent::withMaxSteps(10)`. */
    private const int MAX_STEPS = 10;

    /**
     * @return array<string, mixed>
     */
    public function grade(EvalCase $case, EvalResult $result): array
    {
        $output = $result->output ?? [];
        $payload = is_array($output['structured'] ?? null) ? $output['structured'] : null;
        $guidelines = $this->guidelines($payload);
        $schemaIssues = $payload === null ? [] : $this->schemaIssues($payload, $guidelines);
        $numbers = RecommendationParser::fromGuidelines($guidelines);

        $scores = [
            'completed' => ! $result->failed(),
            'finished_stop' => $result->finish_reason === 'Stop',
            'latency_ms' => $result->latency_ms,
            'prompt_tokens' => data_get($result->usage, 'promptTokens'),
            'cached_prompt_tokens' => data_get($result->usage, 'cachedPromptTokens'),
            'completion_tokens' => data_get($result->usage, 'completionTokens'),
            'reasoning_tokens' => data_get($result->usage, 'reasoningTokens'),
            'text_present' => trim((string) ($output['text'] ?? '')) !== '',

            // A null payload means the transformation to structured output never
            // produced anything usable — the failure production surfaces to the
            // reviewer as an error message instead of recommendations.
            'structured_transform_ok' => $payload !== null,

            // A call that never came back (a timeout, a gateway error) says
            // nothing about the shape a model answers in. `completed` already
            // counts it, so it is left out here rather than counted twice.
            'schema_valid' => $result->failed() ? null : $payload !== null && $schemaIssues === [],
            'schema_issue_count' => count($schemaIssues),
            'schema_issues' => $schemaIssues,

            'guideline_count' => count($guidelines),
            'guideline_ids_returned' => count($numbers['guideline_ids']),
            'unknown_guideline_count' => $this->unknownGuidelineCount($numbers['guideline_ids']),
            'malformed_number_count' => count($numbers['malformed_numbers']),
            'feedback_present' => trim((string) ($payload['feedback'] ?? '')) !== '',
        ];

        if ($this->agentOf($case) === Agents::GuidelineRecommender) {
            $steps = (int) ($output['steps'] ?? 0);

            $scores['steps'] = $steps;
            $scores['max_steps_hit'] = $steps >= self::MAX_STEPS;
            $scores['tool_call_count'] = count($result->tool_calls ?? []);

            // Read from the recommender's own text, before StructuredOutput has
            // had a chance to repair or collapse a combined number.
            $scores['combined_number_count'] = $result->failed()
                ? null
                : count(RecommendationParser::combinedNumbers($output['text'] ?? null));
        }

        return $scores;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<int, array<string, mixed>>
     */
    private function guidelines(?array $payload): array
    {
        $guidelines = $payload['guidelines'] ?? [];

        if (! is_array($guidelines)) {
            return [];
        }

        return array_values(array_map(
            fn ($guideline) => (array) $guideline,
            array_filter($guidelines, fn ($guideline) => is_array($guideline) || is_object($guideline))
        ));
    }

    /**
     * Every way the payload departs from the schema production asked for.
     *
     * The enum values are checked here rather than left to
     * `mapResponseToItemArray()`: `NamedEnum::fromName()` returns null for an
     * unrecognised name instead of throwing, so an unmappable `assessment` or
     * `impact` would otherwise pass silently and only surface as a null column.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<int, array<string, mixed>>  $guidelines
     * @return array<int, string>
     */
    private function schemaIssues(array $payload, array $guidelines): array
    {
        $issues = [];
        $rawGuidelines = $payload['guidelines'] ?? null;

        if (! is_array($rawGuidelines) || ! array_is_list($rawGuidelines)) {
            $issues[] = 'guidelines is not a list';
        } elseif (count($rawGuidelines) !== count($guidelines)) {
            // `guidelines()` drops members that are not objects so the field
            // checks below can run; they are still a departure from the schema.
            $issues[] = sprintf('guidelines has %d entries that are not objects', count($rawGuidelines) - count($guidelines));
        }

        if (! is_string($payload['feedback'] ?? null)) {
            $issues[] = 'feedback is missing or not a string';
        }

        $required = GuidelinesAnalyzerService::ISSUE_SCHEMA['required'];
        $properties = GuidelinesAnalyzerService::ISSUE_SCHEMA['properties'];

        foreach ($guidelines as $index => $guideline) {
            foreach ($required as $field) {
                if (($guideline[$field] ?? null) === null || $guideline[$field] === '') {
                    $issues[] = "guideline $index: missing $field";
                }
            }

            foreach ($properties as $field => $definition) {
                $allowed = $definition['enum'] ?? null;
                $value = $guideline[$field] ?? null;

                if ($allowed && $value !== null && ! in_array($value, $allowed, true)) {
                    $issues[] = "guideline $index: $field is not one of ".implode('|', $allowed);
                }
            }
        }

        return $issues;
    }

    /**
     * Ids the model named that do not resolve to a guideline, using the same
     * check production applies before storing an issue.
     *
     * @param  array<int, int>  $ids
     */
    private function unknownGuidelineCount(array $ids): int
    {
        return count(array_filter(
            $ids,
            fn (int $id) => ! GuidelinesAnalyzerService::isValidGuidelineId($id)
        ));
    }

    private function agentOf(EvalCase $case): ?Agents
    {
        return Agents::tryFrom($case->agent?->name ?? '');
    }
}
