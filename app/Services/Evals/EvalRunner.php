<?php

namespace App\Services\Evals;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\Agents\StructuredOutputAgent;
use App\Ai\Prism\PrismAction;
use App\Ai\Prism\PrismSchema;
use App\Ai\Prism\Tools\ScratchPadTool;
use App\Enums\Agents;
use App\Enums\EvalRunStatus;
use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use App\Models\Scope;
use App\Services\Evals\Graders\AgreementGrader;
use App\Services\Evals\Graders\StructuralGrader;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Text\Response as TextResponse;
use Prism\Prism\Tool;
use Prism\Prism\ValueObjects\Usage;
use RuntimeException;
use Throwable;

/**
 * Replays evaluation cases against whichever models a run specifies.
 *
 * Every replay runs inside a transaction that is always rolled back, so the
 * chat history each agent writes and any issue a tool creates leave no residue.
 * The one tool that writes outside the database, the recommender's scratch
 * pad, is swapped for `ReplayScratchPadTool`. Results are buffered in memory
 * and persisted only after the rollback.
 */
class EvalRunner
{
    use PrismAction;
    use PrismSchema;

    public function __construct(
        private readonly StructuralGrader $structural,
        private readonly AgreementGrader $agreement,
    ) {}

    /**
     * Replay every case in a run, `$run->repeats` times each, and mark the run
     * finished. Returns how many replays errored.
     *
     * A run that produced results is completed even if some cases errored — an
     * error rate is one of the things being measured. Only a run where nothing
     * succeeded is a failed run.
     *
     * @param  Collection<int, EvalCase>  $cases
     * @param  (Closure(EvalCase, EvalResult): void)|null  $onResult
     */
    public function replay(EvalRun $run, Collection $cases, ?Closure $onResult = null): int
    {
        $repeats = max(1, (int) $run->repeats);
        $failures = 0;

        $run->markRunning();

        foreach ($cases as $case) {
            for ($repeat = 0; $repeat < $repeats; $repeat++) {
                $result = $this->runCase($run, $case, $repeat);

                if ($result->failed()) {
                    $failures++;
                }

                if ($onResult) {
                    $onResult($case, $result);
                }
            }
        }

        $total = $cases->count() * $repeats;
        $run->markFinished($failures === $total ? EvalRunStatus::Failed : EvalRunStatus::Completed);

        return $failures;
    }

    /**
     * Re-score every result in a run without calling a model, e.g. after a
     * grader change or a corrected label.
     */
    public function regrade(EvalRun $run): void
    {
        $results = $run->results()->with('evalCase.agent')->orderBy('id')->get();

        foreach ($results as $result) {
            if ($result->evalCase) {
                $result->mergeScores($this->grade($result->evalCase, $result));
            }
        }
    }

    /**
     * Replay one case once, persist the outcome and score it.
     *
     * A failure is recorded as a result carrying an `error`, never thrown — an
     * unreliable model is a finding, not a reason to abandon the run.
     */
    public function runCase(EvalRun $run, EvalCase $case, int $repeatIndex = 0): EvalResult
    {
        $this->applyModels($run->models ?? []);

        $start = microtime(true);

        try {
            $outcome = $this->isolate($case, fn () => $this->invokeAgent($case));
        } catch (Throwable $e) {
            $outcome = [
                'output' => null,
                'usage' => null,
                'finish_reason' => null,
                'tool_calls' => [],
                'error' => Str::limit(get_class($e).': '.$e->getMessage(), 2000),
            ];
        }

        $result = EvalResult::create([
            'eval_run_id' => $run->getKey(),
            'eval_case_id' => $case->getKey(),
            'repeat_index' => $repeatIndex,
            'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ...$outcome,
        ]);

        // Both graders are free and deterministic, so there is no reason to
        // make the caller pay for a second pass over the run to get them.
        $result->mergeScores($this->grade($case, $result));

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function grade(EvalCase $case, EvalResult $result): array
    {
        return array_merge(
            $this->structural->grade($case, $result),
            $this->agreement->grade($case, $result),
        );
    }

    /**
     * Point the chat profiles at this run's models.
     *
     * Both agents read `config('cornell_ai.profiles')` inside their
     * constructors, so overriding config is the whole of the model swap.
     *
     * @param  array<string, mixed>  $models
     */
    public function applyModels(array $models): void
    {
        if ($provider = $models['provider'] ?? null) {
            config()->set('cornell_ai.prism_provider', $provider);
        }

        foreach ($models['profiles'] ?? [] as $profile => $model) {
            config()->set("cornell_ai.profiles.$profile.model", $model);
        }
    }

    /**
     * Run the work as the case's original user, inside a transaction that is
     * always rolled back.
     *
     * The user matters twice over: `PrismHistory::storeChatHistory()` writes a
     * non-nullable `user_id`, and tools resolve their own authorization, so an
     * unauthenticated replay would not behave as production did.
     *
     * @return array<string, mixed>
     */
    private function isolate(EvalCase $case, Closure $work): array
    {
        if ($case->user_id) {
            Auth::onceUsingId($case->user_id);
        }

        DB::beginTransaction();

        try {
            return $work();
        } finally {
            DB::rollBack();
            Auth::forgetUser();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function invokeAgent(EvalCase $case): array
    {
        $agent = Agents::tryFrom($case->agent?->name ?? '');

        return match ($agent) {
            Agents::StructuredOutput => $this->runStructuredOutput($case),
            Agents::GuidelineRecommender => $this->runRecommender($case),
            default => throw new InvalidArgumentException(
                "Case {$case->getKey()} names an agent this runner cannot replay."
            ),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function runStructuredOutput(EvalCase $case): array
    {
        $data = $case->input['data'] ?? null;

        if (! is_string($data) || $data === '') {
            throw new RuntimeException('Case carries no input data to transform.');
        }

        $response = StructuredOutputAgent::for(
            schema: $this->recommendedGuidelinesSchema(),
            data: $data,
            contextModel: $this->contextModel($case),
        )->getResponse();

        // Production reads the *text* and decodes it (`PrismSchema::getStructuredResponse()`)
        // rather than trusting Prism's `structured` convenience, so a model whose
        // provider ignores strict schema mode fails here exactly as it would live.
        $decoded = $this->decodeStructured($response->text);

        return [
            'output' => [
                'text' => $response->text,
                'structured' => $decoded['payload'],
                'structured_error' => $decoded['error'],
                'guideline_ids' => $this->guidelineIds($decoded['payload'], $response->text),
            ],
            'usage' => $this->usageArray($response->usage),
            'finish_reason' => $response->finishReason?->name,
            'tool_calls' => [],
            'error' => null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function runRecommender(EvalCase $case): array
    {
        $scope = Scope::find($case->input['scope_id'] ?? null);

        if (! $scope) {
            throw new RuntimeException('The scope this case replays against no longer exists.');
        }

        $agent = GuidelineRecommenderAgent::for($scope)
            ->withPrompt((string) ($case->input['prompt'] ?? ''));

        $agent->withTools(array_map(
            fn (Tool $tool) => $tool instanceof ScratchPadTool ? new ReplayScratchPadTool : $tool,
            $agent->toRequest()->tools()
        ));

        $response = $this->streamToResponse($agent);

        // Production stores the history before transforming, and the nested
        // StructuredOutputAgent is given the same context model, so the write is
        // reproduced here too — the transaction discards it either way.
        $agent->storeResponse($response);

        // Production stops here on a stream error and shows the text as the error
        // rather than transforming it, so a failed stream is recorded as a failed
        // replay instead of as a transformation that produced nothing.
        $streamErrored = $response->finishReason === FinishReason::Error;

        $transform = $streamErrored
            ? ['payload' => null, 'error' => null]
            : $this->transformRecommendation($response, $scope);

        return [
            'output' => [
                'text' => $response->text,
                'structured' => $transform['payload'],
                'structured_error' => $transform['error'],
                'guideline_ids' => $this->guidelineIds($transform['payload'], $response->text),
                'steps' => $response->steps->count(),
            ],
            'usage' => $this->usageArray($response->usage),
            'finish_reason' => $response->finishReason?->name,
            'tool_calls' => $response->steps
                ->flatMap(fn ($step) => array_map(fn ($call) => $call->name, $step->toolCalls))
                ->values()
                ->all(),
            'error' => $streamErrored ? Str::limit((string) $response->text, 2000) : null,
        ];
    }

    /**
     * Drive the real production streaming path rather than `asText()`, so a
     * model that misbehaves mid-stream shows up here as it would in the UI.
     */
    private function streamToResponse(GuidelineRecommenderAgent $agent): TextResponse
    {
        $builder = null;

        foreach ($this->collectStream($agent->toRequest(), $agent->asStream()) as $streamed) {
            $builder = $streamed;
        }

        $response = $builder?->toResponse();

        if (! $response) {
            throw new RuntimeException('No response received from AI.');
        }

        return $response;
    }

    /**
     * Run the recommendation through `StructuredOutputAgent`, exactly as
     * `handleResponse()` does, but keeping the failure as a measurable outcome
     * instead of a feedback string — and without the side-effecting callback.
     *
     * This is where the Task-profile model reaches the recommender arm, so a
     * recommender run is never a test of the Chat model alone.
     *
     * @return array{payload: array<string, mixed>|null, error: string|null}
     */
    private function transformRecommendation(TextResponse $response, Model $context): array
    {
        try {
            return [
                'payload' => $this->toArray(
                    $this->getStructuredResponse($response, $this->recommendedGuidelinesSchema(), $context)
                ),
                'error' => null,
            ];
        } catch (Throwable $e) {
            return ['payload' => null, 'error' => Str::limit($e->getMessage(), 1000)];
        }
    }

    /**
     * @return array{payload: array<string, mixed>|null, error: string|null}
     */
    private function decodeStructured(?string $text): array
    {
        $decoded = json_decode((string) $text, true);

        if (! is_array($decoded)) {
            return ['payload' => null, 'error' => 'Error parsing structured output: '.json_last_error_msg()];
        }

        return ['payload' => $decoded, 'error' => null];
    }

    private function recommendedGuidelinesSchema(): Schema
    {
        return self::convertToPrismSchema(GuidelinesAnalyzerService::getRecommendedGuidelinesSchema());
    }

    /**
     * The context model is only used to tag the discarded chat history, so a
     * context that has since been deleted is not worth failing the replay over.
     */
    private function contextModel(EvalCase $case): ?Model
    {
        $type = $case->context_type;

        if (! $type || ! class_exists($type) || ! $case->context_id) {
            return null;
        }

        return $type::find($case->context_id);
    }

    /**
     * Ids from the transformed payload where it survived, falling back to the
     * raw text so a failed transformation still reports what the model named.
     *
     * @param  array<string, mixed>|null  $payload
     * @return array<int, int>
     */
    private function guidelineIds(?array $payload, ?string $text): array
    {
        if ($payload !== null) {
            return RecommendationParser::fromGuidelines((array) ($payload['guidelines'] ?? []))['guideline_ids'];
        }

        return RecommendationParser::guidelineIds($text);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toArray(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return json_decode(json_encode($value), true);
    }

    /**
     * @return array<string, int|null>|null
     */
    private function usageArray(?Usage $usage): ?array
    {
        if (! $usage) {
            return null;
        }

        return [
            'promptTokens' => $usage->promptTokens,
            'completionTokens' => $usage->completionTokens,
        ];
    }
}
