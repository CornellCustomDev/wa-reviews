<?php

namespace App\Console\Commands\Evals;

use App\Enums\Agents;
use App\Enums\ChatProfile;
use App\Enums\EvalRunStatus;
use App\Models\Agent;
use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use App\Services\Evals\CaseExtractor;
use App\Services\Evals\EvalRunner;
use App\Services\Evals\ReportBuilder;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

use function Laravel\Prompts\suggest;

/**
 * The one command for answering "is this model safe to switch to?".
 *
 * It builds the question set if there is none, replays the production model as
 * the baseline if there is none, replays the candidate, and writes the
 * comparison. Every step delegates to the services, so nothing here is logic
 * that a test of those services does not already cover.
 */
class EvalModel extends Command
{
    protected $signature = 'ai:eval
        {--agent=StructuredOutput : The AI feature to evaluate: StructuredOutput (cheap) or GuidelineRecommender (large pages, slow).}
        {--model= : The candidate model. Prompted for when omitted.}
        {--profile= : Which chat profile the model replaces, chat or task. Defaults to the profile the agent reads, so only one thing changes.}
        {--baseline : Replay this model (default: the configured production model) as the new baseline instead of as a candidate.}
        {--repeats=2 : Replays per case. Two or more separates a model difference from sampling noise.}
        {--limit=20 : Cases to extract when the agent has no dataset yet.}
        {--dataset-version= : The frozen question set to use. Defaults to the agent\'s most recent one, extracting a new one if there is none.}
        {--margins= : JSON of non-inferiority margins per metric. Defaults to the baseline\'s margins, then to the agent\'s standard ones.}
        {--label= : Name for the run. Defaults to the model.}
        {--out= : Where to write the report. Defaults to storage/eval-report-<model>-<date>-<run>.md.}
        {--dry-run : Show what would run without calling a model or writing anything.}';

    protected $description = 'Evaluate a candidate model against the production model on real past cases.';

    /**
     * What each agent is held to when no margins are given: a usable answer
     * every time, and no loss on the quality metrics it produces.
     *
     * StructuredOutput is a copy job, so every fidelity check is gated: the
     * per-field text, the summary, and the exact set of guidelines. A model
     * could otherwise rewrite the summary or append an invented guideline while
     * copying every other field perfectly.
     *
     * @var array<string, array<string, array{margin: float}>>
     */
    private const array DEFAULT_MARGINS = [
        Agents::StructuredOutput->value => [
            'schema_valid' => ['margin' => 0.0],
            'field_match_rate' => ['margin' => 0.0],
            'feedback_match' => ['margin' => 0.0],
            'guideline_ids_exact' => ['margin' => 0.0],
        ],
        Agents::GuidelineRecommender->value => [
            'finished_stop' => ['margin' => 0.05],
            'f1_strict' => ['margin' => 0.02],
        ],
    ];

    public function handle(CaseExtractor $extractor, EvalRunner $runner, ReportBuilder $builder): int
    {
        $agent = Agents::tryFrom((string) $this->option('agent'));

        if (! $agent || ! in_array($agent->value, CaseExtractor::SUPPORTED_AGENTS, true)) {
            $this->error(sprintf('Unsupported agent "%s". Supported: %s.', $this->option('agent'), implode(', ', CaseExtractor::SUPPORTED_AGENTS)));

            return self::FAILURE;
        }

        $cases = $this->cases($extractor, $agent);

        if ($cases === null) {
            return self::FAILURE;
        }

        if ($cases->isEmpty()) {
            return self::SUCCESS;
        }

        $datasetVersion = $cases->first()->dataset_version;
        $baseline = EvalRun::baselineFor($datasetVersion);
        $profile = $this->profile($agent);
        $productionModel = config("cornell_ai.profiles.{$profile}.model");

        if ($this->option('baseline')) {
            $model = $this->option('model') ?: $productionModel;
            $this->plan($cases, $profile, $model, null, asBaseline: true);

            if ($this->option('dry-run')) {
                return self::SUCCESS;
            }

            $run = $this->replay($runner, $cases, $profile, $model, null, $this->margins($agent, $baseline), asBaseline: true);

            if ($run->status === EvalRunStatus::Failed) {
                return self::FAILURE;
            }

            $this->components->info("Run {$run->getKey()} is now the baseline for dataset \"{$datasetVersion}\". Run ai:eval --model=<candidate> to test against it.");

            return self::SUCCESS;
        }

        $model = $this->option('model') ?: $this->askForModel($productionModel);

        if (! $baseline) {
            $this->components->info("No baseline for dataset \"{$datasetVersion}\" yet; the production model ({$productionModel}) will be replayed first.");
            $this->plan($cases, $profile, $productionModel, null, asBaseline: true);
        }

        $this->plan($cases, $profile, $model, $baseline, asBaseline: false);

        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        $margins = $this->margins($agent, $baseline);
        $baseline ??= $this->replay($runner, $cases, $profile, $productionModel, null, $margins, asBaseline: true);

        if ($baseline->status === EvalRunStatus::Failed) {
            $this->error('The baseline replay produced no usable results, so there is nothing to compare a candidate against.');

            return self::FAILURE;
        }

        $candidate = $this->replay($runner, $cases, $profile, $model, $baseline, $margins, asBaseline: false);

        $report = $builder->build($candidate, $baseline);
        $markdown = $builder->toMarkdown($report);
        $path = $this->option('out') ?: storage_path(sprintf('eval-report-%s-%s-%d.md', Str::slug($model), now()->toDateString(), $candidate->getKey()));

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $markdown);

        $this->newLine();
        $this->line($markdown);
        $this->components->info("Written to {$path}");

        return $report['verdict'] === 'fail' ? self::FAILURE : self::SUCCESS;
    }

    /**
     * The cases to replay: the requested or most recent dataset for this agent,
     * extracted fresh when the agent has no cases under that name yet. Null when
     * nothing can be replayed; empty when a dry run stopped short of extracting.
     *
     * @return Collection<int, EvalCase>|null
     */
    private function cases(CaseExtractor $extractor, Agents $agent): ?Collection
    {
        $agentId = Agent::findAgent($agent)?->getKey();
        $version = $this->option('dataset-version') ?: $this->latestDatasetVersion($agentId) ?? $this->newDatasetVersion();
        $cases = $this->casesIn($version, $agentId);

        if ($cases->isNotEmpty()) {
            $this->components->info(sprintf('Dataset %s: %d %s cases.', $version, $cases->count(), $agent->value));

            return $cases;
        }

        $limit = (int) $this->option('limit') ?: null;

        if ($this->option('dry-run')) {
            $this->components->info("No {$agent->value} cases in dataset \"{$version}\" yet; up to {$limit} would be extracted.");

            return new Collection;
        }

        $this->components->info("No {$agent->value} cases in dataset \"{$version}\" yet; extracting up to {$limit}.");

        $cases = $extractor->extractDataset($agent, $version, $limit);

        foreach ($extractor->drainSkipReasons() as $reason => $count) {
            $this->line("  skipped {$count} × {$reason}");
        }

        if ($cases->isEmpty()) {
            $this->error('No replayable transcripts were found for this agent.');

            return null;
        }

        $this->components->info(sprintf('Stored %d cases.', $cases->count()));

        return $this->casesIn($version, $agentId);
    }

    /**
     * @return Collection<int, EvalCase>
     */
    private function casesIn(string $version, ?int $agentId): Collection
    {
        return EvalCase::with('agent')
            ->where('dataset_version', $version)
            ->where('agent_id', $agentId)
            ->orderBy('id')
            ->get();
    }

    private function latestDatasetVersion(?int $agentId): ?string
    {
        return EvalCase::query()->where('agent_id', $agentId)->latest('id')->value('dataset_version');
    }

    /**
     * Today's date, suffixed if a dataset by that name already exists. Dataset
     * versions are never rebuilt in place: two runs are only comparable when
     * they answered the same frozen questions.
     */
    private function newDatasetVersion(): string
    {
        $base = now()->toDateString();
        $version = $base;

        for ($suffix = 2; EvalCase::where('dataset_version', $version)->exists(); $suffix++) {
            $version = "{$base}-{$suffix}";
        }

        return $version;
    }

    /**
     * The profile the candidate model replaces. Only one profile moves per
     * run so a difference can be attributed: the recommender's own calls use
     * Chat, and StructuredOutput (nested or standalone) uses Task.
     */
    private function profile(Agents $agent): string
    {
        $requested = $this->option('profile');

        if ($requested) {
            return (ChatProfile::tryFrom($requested) ?? throw new InvalidArgumentException("Unknown profile \"{$requested}\"; use chat or task."))->value;
        }

        return $agent === Agents::GuidelineRecommender ? ChatProfile::Chat->value : ChatProfile::Task->value;
    }

    private function askForModel(string $productionModel): string
    {
        $seen = EvalRun::query()->pluck('models')
            ->flatMap(fn ($models) => array_values((array) data_get($models, 'profiles', [])))
            ->push($productionModel)
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return suggest(
            label: 'Which model do you want to test?',
            options: $seen,
            required: true,
            hint: "Production currently uses {$productionModel}.",
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function margins(Agents $agent, ?EvalRun $baseline): array
    {
        if ($json = $this->option('margins')) {
            $decoded = json_decode((string) $json, true);

            if (! is_array($decoded)) {
                $this->warn('Could not parse --margins as JSON; using the defaults instead.');
            } else {
                return $decoded;
            }
        }

        return $baseline?->margins ?: self::DEFAULT_MARGINS[$agent->value];
    }

    /**
     * The full model snapshot for a run, with one profile replaced.
     *
     * A candidate starts from the baseline's snapshot rather than today's
     * config, so that if the provider or the other profile has moved since the
     * baseline ran, the comparison still changes exactly one thing. A baseline
     * starts from the configured production models.
     *
     * @return array<string, mixed>
     */
    private function models(string $profile, string $model, ?EvalRun $baseline): array
    {
        $configured = [];

        foreach ([ChatProfile::Chat, ChatProfile::Task] as $chatProfile) {
            $configured[$chatProfile->value] = config("cornell_ai.profiles.{$chatProfile->value}.model");
        }

        $snapshot = [
            'provider' => data_get($baseline?->models, 'provider') ?? config('cornell_ai.prism_provider'),
            'profiles' => array_merge($configured, (array) data_get($baseline?->models, 'profiles', [])),
        ];

        $snapshot['profiles'][$profile] = $model;

        return $snapshot;
    }

    /**
     * @param  Collection<int, EvalCase>  $cases
     */
    private function plan(Collection $cases, string $profile, string $model, ?EvalRun $baseline, bool $asBaseline): void
    {
        $repeats = max(1, (int) $this->option('repeats'));
        $models = $this->models($profile, $model, $baseline);

        $this->newLine();
        $this->components->twoColumnDetail(
            $asBaseline ? '<options=bold>Baseline run</>' : '<options=bold>Candidate run</>',
            sprintf('%d cases × %d repeats = %d calls', $cases->count(), $repeats, $cases->count() * $repeats),
        );

        foreach ($models['profiles'] as $name => $profileModel) {
            $this->components->twoColumnDetail("  {$name}", $name === $profile ? "<options=bold>{$profileModel}</>" : $profileModel);
        }

        if ($cases->first()?->agent?->name === Agents::GuidelineRecommender->value) {
            $this->line('  Large stored pages: expect ~100k prompt tokens per call.');
        }
    }

    /**
     * @param  Collection<int, EvalCase>  $cases
     * @param  array<string, mixed>  $margins
     */
    private function replay(EvalRunner $runner, Collection $cases, string $profile, string $model, ?EvalRun $baseline, array $margins, bool $asBaseline): EvalRun
    {
        $run = EvalRun::create([
            'label' => $this->option('label') ?: ($asBaseline ? "baseline-{$model}" : "candidate-{$model}"),
            'dataset_version' => $cases->first()->dataset_version,
            'status' => EvalRunStatus::Pending,
            'repeats' => max(1, (int) $this->option('repeats')),
            'models' => $this->models($profile, $model, $baseline),
            'margins' => $margins,
            'git_sha' => $this->gitSha(),
        ]);

        $this->newLine();
        $this->components->info("Replaying run {$run->getKey()} — {$run->label}");

        $failures = $runner->replay($run, $cases, function (EvalCase $case, EvalResult $result) {
            $this->line(sprintf(
                '  case %d/%d — %s %dms%s',
                $case->getKey(),
                $result->repeat_index,
                $result->failed() ? 'ERROR' : ($result->finish_reason ?? 'no finish reason'),
                $result->latency_ms,
                $result->failed() ? ': '.$result->error : ($result->score('schema_valid') ? ' schema ok' : ' SCHEMA INVALID'),
            ));
        });

        $this->components->twoColumnDetail('  errors', "{$failures} / ".($cases->count() * $run->repeats));

        // A baseline is promoted only once it has usable results; a run where
        // every call errored must not displace the baseline that worked.
        if ($asBaseline && $run->status !== EvalRunStatus::Failed) {
            $run->markAsBaseline();
        } elseif ($asBaseline) {
            $this->error("Run {$run->getKey()} errored on every call and was not made the baseline.");
        }

        return $run;
    }

    private function gitSha(): ?string
    {
        try {
            $result = Process::run('git rev-parse --short HEAD');

            return $result->successful() ? trim($result->output()) : null;
        } catch (Throwable) {
            return null;
        }
    }
}
