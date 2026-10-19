<?php

namespace Tests\Feature\Evals;

use App\Services\Evals\Statistics;
use PHPUnit\Framework\TestCase;

/**
 * The statistics are checked against values computed by hand, not against the
 * implementation's own output — a test that only pins current behaviour would
 * happily preserve a wrong formula.
 */
class StatisticsTest extends TestCase
{
    private Statistics $statistics;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statistics = new Statistics;
    }

    public function test_the_bootstrap_reports_the_mean_difference_and_a_spanning_interval(): void
    {
        $differences = [0.1, 0.2, 0.15, 0.05, 0.25, 0.2, 0.1, 0.3];

        $result = $this->statistics->pairedBootstrap($differences);

        $this->assertSame(8, $result['n']);
        $this->assertSame(0.16875, $result['mean']);
        $this->assertLessThan($result['mean'], $result['ci_low']);
        $this->assertGreaterThan($result['mean'], $result['ci_high']);
        $this->assertTrue($result['excludes_zero'], 'Every case improved, so zero is not a plausible mean.');
    }

    public function test_an_interval_spanning_zero_is_reported_as_inconclusive(): void
    {
        $result = $this->statistics->pairedBootstrap([-0.4, 0.5, -0.3, 0.35, 0.1, -0.2]);

        $this->assertFalse($result['excludes_zero']);
        $this->assertLessThan(0, $result['ci_low']);
        $this->assertGreaterThan(0, $result['ci_high']);
    }

    public function test_identical_results_give_a_zero_width_interval(): void
    {
        $result = $this->statistics->pairedBootstrap([0.0, 0.0, 0.0, 0.0]);

        $this->assertSame(0.0, $result['mean']);
        $this->assertSame(0.0, $result['ci_low']);
        $this->assertSame(0.0, $result['ci_high']);
        $this->assertFalse($result['excludes_zero']);
    }

    public function test_the_bootstrap_is_reproducible(): void
    {
        $differences = [0.3, -0.1, 0.4, 0.0, 0.2, -0.25, 0.15];

        $this->assertSame(
            $this->statistics->pairedBootstrap($differences),
            $this->statistics->pairedBootstrap($differences),
            'A report re-run on unchanged data must produce the same interval.'
        );
    }

    public function test_a_single_case_cannot_produce_an_interval(): void
    {
        $result = $this->statistics->pairedBootstrap([0.5]);

        $this->assertSame(0.5, $result['mean']);
        $this->assertNull($result['ci_low'], 'One case gives a point estimate and nothing more.');
    }

    public function test_no_cases_at_all_is_reported_rather_than_divided_by_zero(): void
    {
        $result = $this->statistics->pairedBootstrap([]);

        $this->assertSame(0, $result['n']);
        $this->assertNull($result['mean']);
    }

    public function test_standard_deviation_is_the_sample_form(): void
    {
        // Sample sd of 2, 4, 4, 4, 5, 5, 7, 9 is 2.13809 (n−1 in the divisor);
        // the population form would give 2.
        $this->assertSame(2.1381, round($this->statistics->standardDeviation([2, 4, 4, 4, 5, 5, 7, 9]), 4));
        $this->assertSame(0.0, $this->statistics->standardDeviation([5.0]));
    }
}
