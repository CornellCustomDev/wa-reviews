<?php

namespace App\Services\Evals;

use App\Enums\Agents;
use App\Models\Agent;
use App\Models\ChatHistory;
use App\Models\EvalCase;
use App\Models\Issue;
use App\Models\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Turns historical `chat_histories` rows into replayable `eval_cases`.
 *
 * The recorded baseline is kept for reference and drift checks only — prompts,
 * the Prism version and the guidelines corpus have all moved since, so a new
 * run is never compared numerically against it. Comparison is always run to run.
 */
class CaseExtractor
{
    /** Only these agents can be replayed today. */
    public const array SUPPORTED_AGENTS = [
        Agents::GuidelineRecommender->value,
        Agents::StructuredOutput->value,
    ];

    /** @var array<int, int> Memoised stored-page lengths, keyed by scope id. */
    private array $pageContentLengths = [];

    /** @var array<string, int> Why cases were rejected, for reporting. */
    private array $skipReasons = [];

    /** Transcripts are large, so full rows are fetched in small batches. */
    private const int BATCH_SIZE = 25;

    public function __construct(private readonly LabelMiner $labelMiner) {}

    /**
     * Build and store a dataset for one agent: every replayable transcript from
     * every source the agent accepts, deduplicated, then sampled across strata
     * down to `$limit`. Call `drainSkipReasons()` afterwards for what was left out.
     *
     * @return Collection<int, EvalCase>
     */
    public function extractDataset(Agents $agent, string $datasetVersion, ?int $limit): Collection
    {
        $extracted = collect();

        foreach ($this->sourceAgents($agent) as $source) {
            $ulids = $this->candidates($source)->pluck('ulid');

            foreach ($ulids->chunk(self::BATCH_SIZE) as $chunk) {
                $histories = ChatHistory::with('agent')->whereIn('ulid', $chunk->all())->get();

                foreach ($histories as $history) {
                    $attributes = $this->extract($history, $datasetVersion, $agent);

                    if ($attributes !== null) {
                        $extracted->push($attributes);
                    }
                }
            }
        }

        return $this->sampleStratified($this->deduplicate($extracted), $limit)
            ->map(fn (array $attributes) => $this->store($attributes));
    }

    /**
     * Which historical agents can seed cases for a target agent.
     *
     * Only two of 597 StructuredOutput transcripts still record the prompt —
     * older Prism versions stored the answer alone. Recommender transcripts are
     * the better source anyway: their final text is *exactly* what production
     * feeds to StructuredOutput.
     *
     * @return array<int, Agents>
     */
    public function sourceAgents(Agents $target): array
    {
        return match ($target) {
            Agents::StructuredOutput => [Agents::StructuredOutput, Agents::GuidelineRecommender],
            default => [$target],
        };
    }

    /**
     * Candidate histories for an agent, newest first, excluding empty rows.
     */
    public function candidates(Agents $agent): Builder
    {
        $agentModel = Agent::findAgent($agent);

        return ChatHistory::query()
            ->where('agent_id', $agentModel?->getKey())
            ->whereNotNull('messages')
            ->whereRaw("JSON_LENGTH(messages, '$.messages') > 0")
            ->latest();
    }

    /**
     * Build the case attributes for one history row, or null when the row cannot
     * be replayed (missing input, or a context model that no longer exists).
     *
     * @param  Agents|null  $target  The agent to build a case for, when it differs
     *                               from the agent that produced the transcript.
     * @return array<string, mixed>|null
     */
    public function extract(ChatHistory $history, string $datasetVersion, ?Agents $target = null): ?array
    {
        $sourceAgent = Agents::tryFrom($history->agent?->name ?? '');
        $target ??= $sourceAgent;

        if (! $sourceAgent || ! $target || ! in_array($target->value, self::SUPPORTED_AGENTS, true)) {
            return $this->skip('unsupported agent');
        }

        if (! in_array($sourceAgent, $this->sourceAgents($target), true)) {
            return $this->skip('wrong source agent');
        }

        $transcript = new TranscriptParser($history->messages ?? []);

        $input = $target === Agents::StructuredOutput
            ? $this->structuredOutputInput($transcript, $sourceAgent)
            : $this->recommenderInput($history, $transcript);

        if ($input === null) {
            return null;
        }

        $derived = $sourceAgent !== $target;

        // A derived case has no historical answer of its own. The input text is
        // the reference instead: StructuredOutput must carry it through intact.
        $baselineText = $derived ? $input['data'] : $transcript->lastAssistantText();

        $labels = $target === Agents::GuidelineRecommender
            ? $this->labelMiner->mine($history)
            : null;

        $parsed = RecommendationParser::parse($baselineText);

        return [
            'dataset_version' => $datasetVersion,
            'agent_id' => Agent::findAgent($target)?->getKey() ?? $history->agent_id,
            'source_chat_history_ulid' => $history->ulid,
            'user_id' => $history->user_id,
            'context_type' => $history->context_type,
            'context_id' => $history->context_id,
            'input' => $input,
            'baseline' => [
                'text' => $baselineText,
                'guideline_ids' => $parsed['guideline_ids'],
                'malformed_numbers' => $parsed['malformed_numbers'],
                'parse_method' => $parsed['parse_method'],
                'usage' => $derived ? null : $transcript->usage(),
                'finish_reason' => $derived ? null : $transcript->finishReason(),
                'tool_calls' => $derived ? [] : $transcript->toolNames(),
                'derived_from' => $derived ? $sourceAgent->value : null,
                'recorded_at' => $history->created_at?->toIso8601String(),
            ],
            'labels' => $labels ? ['mined' => $labels] : null,
            'tags' => $this->tags($history, $target, $transcript, $parsed, $labels, $derived),
        ];
    }

    /**
     * Persist a case, replacing any earlier extraction of the same source row
     * within the same dataset version.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function store(array $attributes): EvalCase
    {
        return EvalCase::updateOrCreate(
            [
                'dataset_version' => $attributes['dataset_version'],
                'agent_id' => $attributes['agent_id'],
                'source_chat_history_ulid' => $attributes['source_chat_history_ulid'],
            ],
            $attributes
        );
    }

    /**
     * Why candidates were rejected, most common first. Draining the tally keeps
     * the report scoped to one agent when several are extracted in a row.
     *
     * @return array<string, int>
     */
    public function drainSkipReasons(): array
    {
        $reasons = $this->skipReasons;
        $this->skipReasons = [];

        arsort($reasons);

        return $reasons;
    }

    private function skip(string $reason): null
    {
        $this->skipReasons[$reason] = ($this->skipReasons[$reason] ?? 0) + 1;

        return null;
    }

    /**
     * Drop cases whose input is byte-identical to one already kept.
     *
     * A StructuredOutput transcript and the recommender run that fed it produce
     * the same payload from two different source rows, and replaying both would
     * double-weight that case in every paired comparison. Earlier entries win,
     * so a case recorded directly is preferred over a derived one.
     *
     * @param  Collection<int, array<string, mixed>>  $cases
     * @return Collection<int, array<string, mixed>>
     */
    public function deduplicate(Collection $cases): Collection
    {
        return $cases->unique(fn (array $case) => md5(json_encode($case['input'] ?? [])))->values();
    }

    /**
     * Round-robin across strata so a limited dataset still spans every shape,
     * rather than over-representing whichever stratum happens to be largest.
     *
     * @param  Collection<int, array<string, mixed>>  $cases
     * @return Collection<int, array<string, mixed>>
     */
    public function sampleStratified(Collection $cases, ?int $limit): Collection
    {
        if ($limit === null || $cases->count() <= $limit) {
            return $cases;
        }

        $strata = $cases
            ->groupBy(fn (array $case) => $this->stratumKey($case['tags'] ?? []))
            ->sortKeys()
            ->map(fn (Collection $group) => $group->values());

        $sampled = collect();

        while ($sampled->count() < $limit) {
            $tookOne = false;

            foreach ($strata as $key => $group) {
                if ($group->isEmpty()) {
                    continue;
                }

                $sampled->push($group->shift());
                $strata[$key] = $group;
                $tookOne = true;

                if ($sampled->count() >= $limit) {
                    break;
                }
            }

            if (! $tookOne) {
                break;
            }
        }

        return $sampled->values();
    }

    /**
     * @param  array<string, mixed>  $tags
     */
    public function stratumKey(array $tags): string
    {
        return implode('|', [
            $tags['agent'] ?? 'unknown',
            ($tags['derived'] ?? false) ? 'derived' : 'direct',
            $tags['context'] ?? 'none',
            $tags['outcome'] ?? 'unknown',
            $tags['finish_reason'] ?? 'unknown',
            $tags['page_size'] ?? 'none',
            ($tags['has_labels'] ?? false) ? 'labelled' : 'unlabelled',
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function structuredOutputInput(TranscriptParser $transcript, Agents $sourceAgent): ?array
    {
        // Own transcript: the `<data>` wrapper is in the user turn. Derived from
        // a recommender run: its final text *is* the data.
        $text = $sourceAgent === Agents::StructuredOutput
            ? $transcript->firstUserText()
            : $transcript->lastAssistantText();

        $data = $text === null ? null : $this->unwrapData($text);

        if ($data === null) {
            return $this->skip('no input text recorded');
        }

        return [
            'data' => $data,
            // `PrismSchema::getStructuredResponse()` has exactly one caller, so
            // every historical row used the recommended-guidelines schema.
            'schema' => 'recommended_guidelines',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function recommenderInput(ChatHistory $history, TranscriptParser $transcript): ?array
    {
        $prompt = $transcript->firstUserText();
        $scopeId = $this->resolveScopeId($history);

        if ($prompt === null) {
            return $this->skip('no prompt recorded');
        }

        if ($scopeId === null) {
            return $this->skip('scope deleted');
        }

        // `FetchScopePageContentTool` is the recommender's whole view of the page.
        // Without the stored HTML both models would analyse an empty string, so
        // the case is not replayable even though every DB row survives.
        if ($this->pageContentLength($scopeId) === 0) {
            return $this->skip('stored page content missing');
        }

        return [
            'prompt' => $prompt,
            'scope_id' => $scopeId,
        ];
    }

    /**
     * The agent is always constructed from a Scope, even when the context model
     * is an Issue. A case is unusable once that Scope is gone.
     */
    private function resolveScopeId(ChatHistory $history): ?int
    {
        if ($history->context_type === Scope::class) {
            return Scope::whereKey($history->context_id)->value('id');
        }

        if ($history->context_type === Issue::class) {
            return Issue::whereKey($history->context_id)->value('scope_id');
        }

        return null;
    }

    private function unwrapData(string $prompt): ?string
    {
        if (preg_match('/<data>\s*(.*?)\s*<\/data>/s', $prompt, $matches)) {
            return $matches[1];
        }

        return trim($prompt) !== '' ? trim($prompt) : null;
    }

    /**
     * @param  array<string, mixed>  $parsed
     * @param  array<string, mixed>|null  $labels
     * @return array<string, mixed>
     */
    private function tags(
        ChatHistory $history,
        Agents $agent,
        TranscriptParser $transcript,
        array $parsed,
        ?array $labels,
        bool $derived = false
    ): array {
        $guidelineIds = $parsed['guideline_ids'];

        return [
            'agent' => $agent->value,
            'derived' => $derived,
            'context' => class_basename($history->context_type ?? 'Anonymous'),
            'outcome' => $guidelineIds === [] ? 'none' : 'guidelines',
            'guideline_count' => count($guidelineIds),
            'has_malformed_numbers' => $parsed['malformed_numbers'] !== [],
            // For a derived case this describes the transcript that produced the
            // input, not an answer: `Length` marks truncated, malformed input,
            // which is exactly the stratum worth keeping.
            'finish_reason' => $transcript->finishReason() ?? 'unknown',
            'tool_pattern' => implode(',', $transcript->toolNames()) ?: 'none',
            'step_count' => count($transcript->toolCalls()),
            // Page size drives recommender cost and difficulty; it says nothing
            // about a StructuredOutput case, whose whole input is the text.
            'page_size' => $agent === Agents::GuidelineRecommender
                ? $this->pageSizeBucket($history)
                : 'n/a',
            'prompt_tokens' => $derived ? null : ($transcript->usage()['promptTokens'] ?? null),
            'has_labels' => (bool) ($labels['triaged_count'] ?? 0),
        ];
    }

    /**
     * Stored page size drives both cost and difficulty for the recommender, so
     * it is worth stratifying on.
     */
    private function pageSizeBucket(ChatHistory $history): string
    {
        $scopeId = $this->resolveScopeId($history);

        if ($scopeId === null) {
            return 'none';
        }

        $length = $this->pageContentLength($scopeId);

        return match (true) {
            $length === 0 => 'none',
            $length < 50_000 => 'small',
            $length < 200_000 => 'medium',
            default => 'large',
        };
    }

    /**
     * Stored page HTML lives on the filesystem; its size comes from storage
     * metadata so extraction never reads hundreds of large pages just to
     * measure them. Memoised because extraction touches the same scope repeatedly.
     */
    private function pageContentLength(int $scopeId): int
    {
        return $this->pageContentLengths[$scopeId] ??= Scope::find($scopeId)?->getPageContentSize() ?? 0;
    }
}
