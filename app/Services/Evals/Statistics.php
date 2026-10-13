<?php

namespace App\Services\Evals;

/**
 * The statistics behind a model-comparison verdict.
 *
 * Every figure here is *paired*: both models answered the same question, so the
 * unit of analysis is the per-case difference, not two independent samples. That
 * pairing is what makes 20 cases enough to say anything at all — it cancels the
 * enormous case-to-case variation in how hard a page is to review.
 *
 * The one test is a paired bootstrap confidence interval on the mean difference.
 * It needs no normality assumption, which matters because latency and token
 * counts are heavily skewed, and it reads the same way for a rate (valid schema,
 * clean finish) as for a scale (F1, latency): if the interval includes zero,
 * nothing has been shown.
 *
 * The bootstrap draws from its own seeded generator, so re-running a report on
 * unchanged data reproduces the interval exactly.
 */
class Statistics
{
    public const int BOOTSTRAP_ITERATIONS = 10000;

    public const int BOOTSTRAP_SEED = 20260731;

    /**
     * Percentile bootstrap confidence interval on the mean paired difference.
     *
     * Resamples whole cases with replacement, which preserves the pairing: a
     * case is either in a replicate or not, never split between models.
     *
     * @param  array<int, float>  $differences  Candidate minus baseline, one per case.
     * @return array{n: int, mean: ?float, ci_low: ?float, ci_high: ?float, excludes_zero: bool}
     */
    public function pairedBootstrap(
        array $differences,
        float $confidence = 0.95,
        int $iterations = self::BOOTSTRAP_ITERATIONS,
        int $seed = self::BOOTSTRAP_SEED,
    ): array {
        $differences = array_values($differences);
        $count = count($differences);

        if ($count === 0) {
            return ['n' => 0, 'mean' => null, 'ci_low' => null, 'ci_high' => null, 'excludes_zero' => false];
        }

        $mean = $this->mean($differences);

        // A single case cannot produce an interval; report the point estimate
        // and stop rather than returning a spuriously tight interval.
        if ($count === 1) {
            return ['n' => 1, 'mean' => round($mean, 6), 'ci_low' => null, 'ci_high' => null, 'excludes_zero' => false];
        }

        $state = $seed;
        $means = [];

        for ($iteration = 0; $iteration < $iterations; $iteration++) {
            $sum = 0.0;

            for ($draw = 0; $draw < $count; $draw++) {
                $sum += $differences[$this->nextIndex($state, $count)];
            }

            $means[] = $sum / $count;
        }

        sort($means);

        $alpha = (1 - $confidence) / 2;
        $low = $this->percentile($means, $alpha);
        $high = $this->percentile($means, 1 - $alpha);

        return [
            'n' => $count,
            'mean' => round($mean, 6),
            'ci_low' => round($low, 6),
            'ci_high' => round($high, 6),
            'excludes_zero' => $low > 0 || $high < 0,
        ];
    }

    /**
     * @param  array<int, float>  $values
     */
    public function mean(array $values): float
    {
        return $values === [] ? 0.0 : array_sum($values) / count($values);
    }

    /**
     * Sample standard deviation, used for the within-case spread across repeats.
     *
     * @param  array<int, float>  $values
     */
    public function standardDeviation(array $values): float
    {
        $count = count($values);

        if ($count < 2) {
            return 0.0;
        }

        $mean = $this->mean($values);
        $sum = 0.0;

        foreach ($values as $value) {
            $sum += ($value - $mean) ** 2;
        }

        return sqrt($sum / ($count - 1));
    }

    /**
     * @param  array<int, float>  $sorted
     */
    private function percentile(array $sorted, float $fraction): float
    {
        $index = (int) floor($fraction * (count($sorted) - 1));

        return $sorted[max(0, min(count($sorted) - 1, $index))];
    }

    /**
     * A deterministic generator local to this class, so a report is reproducible
     * without seeding — and therefore disturbing — the global RNG.
     */
    private function nextIndex(int &$state, int $count): int
    {
        $state = (1664525 * $state + 1013904223) % 4294967296;

        return intdiv($state * $count, 4294967296);
    }
}
