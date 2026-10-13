<?php

namespace App\Console\Commands\Evals;

use App\Models\EvalRun as Run;
use App\Services\Evals\EvalRunner;
use App\Services\Evals\ReportBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class EvalReport extends Command
{
    protected $signature = 'ai:eval-report
        {--run= : The candidate run id. Defaults to the most recent run.}
        {--baseline= : The run to compare against. Defaults to the current baseline run for the candidate\'s dataset version.}
        {--regrade : Re-score both runs first, e.g. after a grader change or a corrected label. Free: no model is called.}
        {--worst=5 : How many per-case regressions to list.}
        {--out= : Write the markdown report to this path as well as printing it.}
        {--json : Emit the raw report as JSON instead of markdown.}';

    protected $description = 'Compare a candidate run against a baseline run and issue a verdict.';

    public function handle(ReportBuilder $builder, EvalRunner $runner): int
    {
        // The candidate may be inferred, the baseline never inferred from thin
        // air: silently comparing a run against itself would produce a
        // confident, meaningless pass. It can still be looked up, but only via
        // the explicit baseline marker set by `ai:eval --baseline`.
        $candidate = $this->option('run')
            ? Run::find((int) $this->option('run'))
            : Run::query()->latest('id')->first();

        if (! $candidate) {
            $this->error('No candidate run found.');

            return self::FAILURE;
        }

        $baseline = $this->option('baseline')
            ? Run::find((int) $this->option('baseline'))
            : Run::baselineFor($candidate->dataset_version);

        if ($baseline && $baseline->is($candidate)) {
            $baseline = null;
        }

        if (! $baseline) {
            $this->error('No baseline run found. Mark one with ai:eval --baseline, or pass one explicitly: ai:eval-report --run=12 --baseline=11');

            return self::FAILURE;
        }

        if (! $this->option('baseline')) {
            $this->components->info("Defaulting --baseline to run {$baseline->getKey()} (the current baseline for dataset \"{$candidate->dataset_version}\").");
        }

        if ($this->option('regrade')) {
            $runner->regrade($baseline);
            $runner->regrade($candidate);
            $this->components->info('Re-scored both runs.');
        }

        $report = $builder->build($candidate, $baseline, (int) $this->option('worst'));

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $markdown = $builder->toMarkdown($report);

        $this->line($markdown);

        if ($path = $this->option('out')) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, $markdown);
            $this->info("Written to $path");
        }

        // A failed verdict is a non-zero exit so this can gate a decision
        // elsewhere; an inconclusive one is not a failure, it is a request for
        // more cases.
        return $report['verdict'] === 'fail' ? self::FAILURE : self::SUCCESS;
    }
}
