<?php

namespace Tests\Feature\Evals;

use App\Enums\Agents;
use App\Enums\ChatProfile;
use App\Enums\EvalRunStatus;
use App\Models\ChatHistory;
use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\TextResponseFake;
use Prism\Prism\Testing\TextStepFake;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\ToolCall;
use Tests\Feature\FeatureTestCase;

class EvalModelTest extends FeatureTestCase
{
    use MakesEvalFixtures;

    private string $reportPath;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cornell_ai.profiles.'.ChatProfile::Chat->value.'.model', 'production-chat');
        config()->set('cornell_ai.profiles.'.ChatProfile::Task->value.'.model', 'production-task');

        $this->reportPath = storage_path('framework/testing/eval-model-'.uniqid().'.md');
    }

    protected function tearDown(): void
    {
        File::delete($this->reportPath);

        parent::tearDown();
    }

    public function test_first_run_extracts_a_dataset_then_replays_production_as_baseline_before_the_candidate(): void
    {
        // Two cases, one repeat, two runs: four model calls in all.
        Prism::fake(array_fill(0, 4, $this->structuredFake()));
        ChatHistory::factory()->structuredOutput()->count(2)->create();

        $this->artisan('ai:eval', [
            '--model' => 'candidate-task',
            '--dataset-version' => 'eval-model-fresh',
            '--limit' => 2,
            '--repeats' => 1,
            '--out' => $this->reportPath,
        ])->assertSuccessful();

        $baseline = EvalRun::where('label', 'baseline-production-task')->latest('id')->firstOrFail();
        $candidate = EvalRun::where('label', 'candidate-candidate-task')->latest('id')->firstOrFail();

        $this->assertSame(2, EvalCase::where('dataset_version', 'eval-model-fresh')->count());
        $this->assertSame('eval-model-fresh', $baseline->dataset_version);
        $this->assertTrue($baseline->is_baseline);
        $this->assertSame('production-task', $baseline->modelForProfile(ChatProfile::Task->value));
        $this->assertSame(2, $baseline->results()->count());

        $this->assertFalse($candidate->is_baseline);
        $this->assertSame($baseline->dataset_version, $candidate->dataset_version);
        $this->assertSame('candidate-task', $candidate->modelForProfile(ChatProfile::Task->value));
        $this->assertSame('production-chat', $candidate->modelForProfile(ChatProfile::Chat->value), 'Only the profile the agent reads is swapped.');
        $this->assertSame(
            ['schema_valid', 'field_match_rate', 'feedback_match', 'guideline_ids_exact'],
            array_keys($candidate->margins),
            'Every fidelity check is gated by default.'
        );

        // The sampled cases come from the shared development database, so how
        // many of them carry a parseable reference varies; identical answers
        // from both runs can still never fail.
        $this->assertMatchesRegularExpression('/\*\*Verdict: (PASS|INCONCLUSIVE)\*\*/', File::get($this->reportPath));
    }

    public function test_a_later_run_reuses_the_current_baseline_and_its_margins(): void
    {
        Prism::fake([$this->structuredFake()]);
        $case = $this->structuredOutputCase('eval-model-reuse');
        $margins = ['schema_valid' => ['margin' => 0.1]];
        $baseline = EvalRun::factory()->create([
            'dataset_version' => 'eval-model-reuse',
            'is_baseline' => true,
            'margins' => $margins,
        ]);
        $runsBefore = EvalRun::count();

        $this->artisan('ai:eval', [
            '--model' => 'candidate-task',
            '--dataset-version' => 'eval-model-reuse',
            '--repeats' => 1,
            '--out' => $this->reportPath,
        ])->assertSuccessful();

        $candidate = EvalRun::latest('id')->firstOrFail();

        $this->assertSame($runsBefore + 1, EvalRun::count(), 'No second baseline is replayed.');
        $this->assertFalse($candidate->is_baseline);
        $this->assertTrue($baseline->refresh()->is_baseline);
        $this->assertSame($margins, $candidate->margins);
        $this->assertSame('candidate-task', $candidate->modelForProfile(ChatProfile::Task->value));
        $this->assertSame(
            $baseline->modelForProfile(ChatProfile::Chat->value),
            $candidate->modelForProfile(ChatProfile::Chat->value),
            'The untouched profile comes from the baseline snapshot, not from whatever config says today.'
        );
        $this->assertSame(1, $candidate->results()->where('eval_case_id', $case->id)->count());
        $this->assertFileExists($this->reportPath);
    }

    public function test_the_baseline_flag_replaces_the_current_baseline_and_writes_no_report(): void
    {
        Prism::fake([$this->structuredFake()]);
        $this->structuredOutputCase('eval-model-rebaseline');
        $previous = EvalRun::factory()->create([
            'dataset_version' => 'eval-model-rebaseline',
            'is_baseline' => true,
        ]);

        $this->artisan('ai:eval', [
            '--baseline' => true,
            '--dataset-version' => 'eval-model-rebaseline',
            '--repeats' => 1,
            '--out' => $this->reportPath,
        ])->assertSuccessful();

        $replacement = EvalRun::latest('id')->firstOrFail();

        $this->assertTrue($replacement->is_baseline);
        $this->assertSame('production-task', $replacement->modelForProfile(ChatProfile::Task->value), 'Without --model the production model is the baseline.');
        $this->assertFalse($previous->refresh()->is_baseline);
        $this->assertFileDoesNotExist($this->reportPath);
    }

    public function test_a_baseline_replay_that_errors_on_every_call_does_not_displace_the_current_baseline(): void
    {
        // A case with nothing to transform errors before any model is called.
        $this->structuredOutputCase('eval-model-broken-baseline')
            ->update(['input' => ['schema' => 'recommended_guidelines', 'data' => '']]);
        $previous = EvalRun::factory()->create([
            'dataset_version' => 'eval-model-broken-baseline',
            'is_baseline' => true,
        ]);

        $this->artisan('ai:eval', [
            '--baseline' => true,
            '--dataset-version' => 'eval-model-broken-baseline',
            '--repeats' => 1,
        ])->assertFailed();

        $failed = EvalRun::latest('id')->firstOrFail();

        $this->assertSame(EvalRunStatus::Failed, $failed->status);
        $this->assertFalse($failed->is_baseline);
        $this->assertTrue($previous->refresh()->is_baseline);
    }

    public function test_a_recommender_candidate_replaces_the_chat_profile(): void
    {
        Prism::fake([
            TextResponseFake::make()
                ->withSteps(collect([
                    TextStepFake::make()
                        ->withToolCalls([new ToolCall('call-1', 'fetch_guidelines', [])])
                        ->withFinishReason(FinishReason::ToolCalls),
                    TextStepFake::make()->withText('Guideline fails.'),
                ]))
                ->withFinishReason(FinishReason::Stop)
                ->withMeta(new Meta('fake', 'fake')),
            $this->structuredFake(),
        ]);
        $this->recommenderCase('eval-model-recommender');
        EvalRun::factory()->create([
            'dataset_version' => 'eval-model-recommender',
            'is_baseline' => true,
            'models' => [
                'provider' => 'cornell',
                'profiles' => [
                    ChatProfile::Chat->value => 'baseline-chat',
                    ChatProfile::Task->value => 'baseline-task',
                ],
            ],
        ]);

        $this->artisan('ai:eval', [
            '--agent' => Agents::GuidelineRecommender->value,
            '--model' => 'candidate-chat',
            '--dataset-version' => 'eval-model-recommender',
            '--repeats' => 1,
            '--out' => $this->reportPath,
        ])->assertSuccessful();

        $candidate = EvalRun::latest('id')->firstOrFail();

        $this->assertSame('candidate-chat', $candidate->modelForProfile(ChatProfile::Chat->value));
        $this->assertSame('baseline-task', $candidate->modelForProfile(ChatProfile::Task->value), 'The nested transform keeps the model the baseline ran with.');
    }

    public function test_a_dry_run_calls_no_model_and_writes_nothing(): void
    {
        Prism::fake([]);
        $this->structuredOutputCase('eval-model-dry');
        $runs = EvalRun::count();
        $results = EvalResult::count();

        $this->artisan('ai:eval', [
            '--model' => 'candidate-task',
            '--dataset-version' => 'eval-model-dry',
            '--dry-run' => true,
            '--out' => $this->reportPath,
        ])->assertSuccessful();

        $this->assertSame($runs, EvalRun::count());
        $this->assertSame($results, EvalResult::count());
        $this->assertFileDoesNotExist($this->reportPath);
    }

    public function test_an_unsupported_agent_is_rejected(): void
    {
        $this->artisan('ai:eval', ['--agent' => 'ModelChatAgent', '--model' => 'x'])->assertFailed();
    }

    private int $guidelineId = 0;

    /**
     * Production decodes the response *text*, so a fake that only sets
     * `structured` would pass here while failing live.
     */
    private function structuredFake(): StructuredResponseFake
    {
        $this->guidelineId = $this->guidelineId ?: $this->makeGuideline()->id;

        $payload = [
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

        return StructuredResponseFake::make()
            ->withText(json_encode($payload))
            ->withStructured($payload);
    }

    private function structuredOutputCase(string $datasetVersion): EvalCase
    {
        return EvalCase::factory()->structuredOutput()->create([
            'dataset_version' => $datasetVersion,
            'user_id' => User::factory()->create()->id,
            'context_type' => Scope::class,
            'context_id' => Scope::factory()->create()->id,
            'input' => ['schema' => 'recommended_guidelines', 'data' => 'Guideline fails: no alt text.'],
        ]);
    }

    private function recommenderCase(string $datasetVersion): EvalCase
    {
        $scope = $this->makeScopeWithPageContent();

        return EvalCase::factory()->create([
            'dataset_version' => $datasetVersion,
            'user_id' => User::factory()->create()->id,
            'context_type' => Scope::class,
            'context_id' => $scope->id,
            'input' => ['scope_id' => $scope->id, 'prompt' => '# Context: Web page scope needing review'],
        ]);
    }
}
