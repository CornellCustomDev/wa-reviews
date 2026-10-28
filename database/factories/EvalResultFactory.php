<?php

namespace Database\Factories;

use App\Models\EvalCase;
use App\Models\EvalResult;
use App\Models\EvalRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvalResult>
 */
class EvalResultFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'eval_run_id' => EvalRun::factory(),
            'eval_case_id' => EvalCase::factory(),
            'repeat_index' => 0,
            'output' => [
                'text' => $this->faker->paragraph,
                'parsed' => null,
            ],
            'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 500],
            'finish_reason' => 'Stop',
            'latency_ms' => $this->faker->numberBetween(500, 30000),
            'tool_calls' => [],
        ];
    }

    public function failed(string $error = 'Connection timed out'): static
    {
        return $this->state(fn () => [
            'output' => null,
            'finish_reason' => null,
            'error' => $error,
        ]);
    }

    /**
     * @param  array<string, int|float|bool|null>  $scores
     */
    public function withScores(array $scores): static
    {
        return $this->state(fn () => ['scores' => $scores]);
    }
}
