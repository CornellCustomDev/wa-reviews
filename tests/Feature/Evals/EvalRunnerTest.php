<?php

namespace Tests\Feature\Evals;

use App\Enums\Agents;
use App\Enums\ChatProfile;
use App\Enums\EvalRunStatus;
use App\Models\Agent;
use App\Models\ChatHistory;
use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\User;
use App\Services\Evals\EvalRunner;
use Illuminate\Database\Eloquent\Collection;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\Testing\TextStepFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\ToolCall;
use Prism\Prism\ValueObjects\Usage;
use Tests\Feature\FeatureTestCase;

class EvalRunnerTest extends FeatureTestCase
{
    use MakesEvalFixtures;

    public function test_it_replays_a_structured_output_case_and_scores_it(): void
    {
        Prism::fake([
            // Prism's structured handler reports cached input separately from
            // `promptTokens`, so 200 uncached + 1000 cached is 1200 sent.
            $this->structuredFake()->withUsage(new Usage(200, 340, cacheReadInputTokens: 1000)),
        ]);

        $case = $this->structuredOutputCase();
        $run = $this->evalRun();

        $result = app(EvalRunner::class)->runCase($run, $case);

        $this->assertNull($result->error);
        $this->assertSame('Stop', $result->finish_reason);
        $this->assertSame(1200, $result->usage['promptTokens'], 'Cached input is still input.');
        $this->assertSame(1000, $result->usage['cachedPromptTokens']);
        $this->assertSame(340, $result->usage['completionTokens']);
        $this->assertGreaterThan(0, $result->latency_ms, 'Latency is a new signal history does not carry.');
        $this->assertSame([$this->guidelineId], $result->output['guideline_ids']);

        $this->assertTrue($result->score('completed'));
        $this->assertTrue($result->score('finished_stop'));
        $this->assertTrue($result->score('schema_valid'));
        $this->assertSame(1, $result->score('guideline_count'));
        $this->assertSame(0, $result->score('unknown_guideline_count'));
        $this->assertTrue($result->score('feedback_present'));

        // Agreement grading runs inline too; this case's reference is prose, so
        // it is recorded as unscoreable rather than left ungraded.
        $this->assertFalse($result->score('agreement_scored'));
        $this->assertSame('reference is prose', $result->scores['agreement_skipped']);
    }

    public function test_it_leaves_no_database_residue(): void
    {
        Prism::fake([$this->structuredFake()]);

        $case = $this->structuredOutputCase();
        $histories = ChatHistory::count();
        $issues = Issue::count();
        $results = EvalResult::count();

        app(EvalRunner::class)->runCase($this->evalRun(), $case);

        $this->assertSame($histories, ChatHistory::count(), 'The agent writes a chat history; the rollback must discard it.');
        $this->assertSame($issues, Issue::count());
        $this->assertSame($results + 1, EvalResult::count(), 'The result itself is persisted after the rollback.');
        $this->assertNull(auth()->user(), 'The replay user must not leak past the case.');
    }

    public function test_it_replays_a_recommender_case_through_the_streaming_path(): void
    {
        Prism::fake([
            TextResponseFake::make()
                ->withSteps(collect([
                    TextStepFake::make()
                        ->withToolCalls([new ToolCall('call-1', 'fetch_guidelines', [])])
                        ->withFinishReason(FinishReason::ToolCalls),
                    TextStepFake::make()->withText('Guideline 9001 fails.'),
                ]))
                ->withFinishReason(FinishReason::Stop)
                ->withUsage(new Usage(98000, 900, cacheReadInputTokens: 60000))
                ->withMeta(new Meta('fake', 'fake')),
            $this->structuredFake(),
        ]);

        $case = $this->recommenderCase();
        $histories = ChatHistory::count();

        $result = app(EvalRunner::class)->runCase($this->evalRun(), $case);

        $this->assertNull($result->error);
        $this->assertSame(['fetch_guidelines'], $result->tool_calls);
        $this->assertStringContainsString('9001 fails', $result->output['text']);
        $this->assertSame([$this->guidelineId], $result->output['guideline_ids']);
        $this->assertNull($result->output['structured_error']);

        $this->assertTrue($result->score('structured_transform_ok'));
        $this->assertTrue($result->score('schema_valid'));
        $this->assertSame(98000, $result->usage['promptTokens'], 'The stream already counts cached input; it must not be added twice.');
        $this->assertSame(1, $result->score('tool_call_count'));
        $this->assertFalse($result->score('max_steps_hit'));
        $this->assertSame($histories, ChatHistory::count(), 'Both the recommender and the nested transform writes are discarded.');
    }

    public function test_a_stream_error_is_recorded_without_attempting_the_transform(): void
    {
        Prism::fake([
            TextResponseFake::make()
                ->withText('upstream gateway timeout')
                ->withFinishReason(FinishReason::Error),
        ]);

        $result = app(EvalRunner::class)->runCase($this->evalRun(), $this->recommenderCase());

        $this->assertStringContainsString('gateway timeout', $result->error);
        $this->assertFalse($result->score('completed'));
        $this->assertFalse($result->score('finished_stop'));
        $this->assertFalse($result->score('structured_transform_ok'));
    }

    public function test_a_failed_transform_is_a_metric_rather_than_a_crash(): void
    {
        Prism::fake([
            TextResponseFake::make()->withText('Prose, not JSON.')->withFinishReason(FinishReason::Stop),
            StructuredResponseFake::make()->withText('also not JSON'),
        ]);

        $result = app(EvalRunner::class)->runCase($this->evalRun(), $this->recommenderCase());

        $this->assertNull($result->error, 'The replay itself succeeded; only the transformation failed.');
        $this->assertTrue($result->score('completed'));
        $this->assertFalse($result->score('structured_transform_ok'));
        $this->assertFalse($result->score('schema_valid'));
    }

    public function test_an_unreplayable_case_is_recorded_as_an_error(): void
    {
        $case = $this->structuredOutputCase();
        $case->update(['input' => ['schema' => 'recommended_guidelines', 'data' => '']]);
        $results = EvalResult::count();

        $result = app(EvalRunner::class)->runCase($this->evalRun(), $case);

        $this->assertStringContainsString('no input data', $result->error);
        $this->assertFalse($result->score('completed'));
        $this->assertNull($result->score('schema_valid'), 'A call that never came back is a reliability failure, not a schema one.');
        $this->assertSame($results + 1, EvalResult::count(), 'A failure is still a result, not a lost case.');
    }

    public function test_it_points_the_chat_profiles_at_the_run_models(): void
    {
        app(EvalRunner::class)->applyModels([
            'provider' => 'test-provider',
            'profiles' => [
                ChatProfile::Chat->value => 'candidate-chat',
                ChatProfile::Task->value => 'candidate-task',
            ],
        ]);

        $this->assertSame('test-provider', config('cornell_ai.prism_provider'));
        $this->assertSame('candidate-chat', config('cornell_ai.profiles.'.ChatProfile::Chat->value.'.model'));
        $this->assertSame('candidate-task', config('cornell_ai.profiles.'.ChatProfile::Task->value.'.model'));
    }

    public function test_it_applies_a_runs_reasoning_effort_and_clears_one_it_does_not_name(): void
    {
        $effortKey = 'cornell_ai.profiles.'.ChatProfile::Chat->value.'.reasoning_effort';
        $runner = app(EvalRunner::class);

        $runner->applyModels([
            'profiles' => [ChatProfile::Chat->value => 'candidate-chat'],
            'reasoning_effort' => [ChatProfile::Chat->value => 'low'],
        ]);

        $this->assertSame('low', config($effortKey));

        $runner->applyModels(['profiles' => [ChatProfile::Chat->value => 'baseline-chat']]);

        $this->assertNull(config($effortKey), 'A run that names no effort must not inherit one from the environment.');
    }

    public function test_replay_answers_every_case_once_per_repeat_and_completes_the_run(): void
    {
        Prism::fake([$this->structuredFake(), $this->structuredFake()]);

        $case = $this->structuredOutputCase();
        $run = $this->evalRun(repeats: 2);
        $repeatsSeen = [];

        $failures = app(EvalRunner::class)->replay(
            $run,
            new Collection([$case]),
            function (EvalCase $replayed, EvalResult $result) use (&$repeatsSeen) {
                $repeatsSeen[] = $result->repeat_index;
            }
        );

        $this->assertSame(0, $failures);
        $this->assertSame([0, 1], $repeatsSeen);
        $this->assertSame([0, 1], $run->results()->pluck('repeat_index')->all());
        $this->assertSame(EvalRunStatus::Completed, $run->refresh()->status);
        $this->assertNotNull($run->finished_at);
    }

    public function test_a_run_where_every_replay_errored_is_marked_failed(): void
    {
        $case = $this->structuredOutputCase();
        $case->update(['input' => ['schema' => 'recommended_guidelines', 'data' => '']]);
        $run = $this->evalRun();

        $failures = app(EvalRunner::class)->replay($run, new Collection([$case]));

        $this->assertSame(1, $failures);
        $this->assertSame(EvalRunStatus::Failed, $run->refresh()->status);
    }

    public function test_regrade_rescores_a_recorded_result_without_calling_a_model(): void
    {
        Prism::fake([]);

        $case = $this->structuredOutputCase();
        $run = $this->evalRun();
        $payload = $this->payload();
        $result = EvalResult::factory()->create([
            'eval_run_id' => $run->id,
            'eval_case_id' => $case->id,
            'output' => ['text' => json_encode($payload), 'structured' => $payload],
            'scores' => ['schema_valid' => false],
        ]);

        app(EvalRunner::class)->regrade($run);

        $result->refresh();

        $this->assertTrue($result->score('schema_valid'), 'The stale score is replaced by what the grader sees now.');
        $this->assertSame(1, $result->score('guideline_count'));
        $this->assertFalse($result->score('agreement_scored'));
    }

    private int $guidelineId = 0;

    /**
     * A payload shaped like the recommended-guidelines schema production asks for.
     *
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        $this->guidelineId = $this->guidelineId ?: $this->makeGuideline()->id;

        return [
            'guidelines' => [[
                'target' => 'Main navigation',
                'reasoning' => 'The images carry meaning.',
                'number' => $this->guidelineId,
                'heading' => 'Images need alt text',
                'criteria' => '1.1.1 Non-text Content',
                'assessment' => 'Fail',
                'observation' => 'The logo has no alt attribute.',
                'recommendation' => 'Add descriptive alt text.',
                'testing' => 'Inspect the img element.',
                'impact' => 'Serious',
            ]],
            'feedback' => 'One issue found.',
        ];
    }

    /**
     * Production decodes the response *text*, so a fake that only sets
     * `structured` would pass here while failing live.
     */
    private function structuredFake(): StructuredResponseFake
    {
        $payload = $this->payload();

        return StructuredResponseFake::make()
            ->withText(json_encode($payload))
            ->withStructured($payload);
    }

    private function structuredOutputCase(): EvalCase
    {
        return EvalCase::factory()->create([
            'dataset_version' => 'runner-test',
            'agent_id' => Agent::firstOrCreate(['name' => Agents::StructuredOutput->value])->id,
            'user_id' => User::factory()->create()->id,
            'context_type' => Scope::class,
            'context_id' => Scope::factory()->create()->id,
            'input' => ['schema' => 'recommended_guidelines', 'data' => 'Guideline 9001 fails: no alt text.'],
        ]);
    }

    private function recommenderCase(): EvalCase
    {
        $scope = $this->makeScopeWithPageContent();

        return EvalCase::factory()->create([
            'dataset_version' => 'runner-test',
            'agent_id' => Agent::firstOrCreate(['name' => Agents::GuidelineRecommender->value])->id,
            'user_id' => User::factory()->create()->id,
            'context_type' => Scope::class,
            'context_id' => $scope->id,
            'input' => ['scope_id' => $scope->id, 'prompt' => '# Context: Web page scope needing review'],
        ]);
    }

    private function evalRun(int $repeats = 1): EvalRun
    {
        return EvalRun::factory()->create([
            'dataset_version' => 'runner-test',
            'repeats' => $repeats,
            'models' => [
                'provider' => config('cornell_ai.prism_provider'),
                'profiles' => [
                    ChatProfile::Chat->value => 'test-chat-model',
                    ChatProfile::Task->value => 'test-task-model',
                ],
            ],
        ]);
    }
}
