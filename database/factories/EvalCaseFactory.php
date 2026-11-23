<?php

namespace Database\Factories;

use App\Enums\Agents;
use App\Models\Agent;
use App\Models\EvalCase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvalCase>
 */
class EvalCaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dataset_version' => 'test-1',
            'agent_id' => fn () => Agent::firstOrCreate(['name' => Agents::GuidelineRecommender->value])->id,
            'input' => [
                'prompt' => $this->faker->paragraph,
            ],
            'baseline' => [
                'text' => $this->faker->paragraph,
                'finish_reason' => 'Stop',
                'usage' => ['prompt_tokens' => 1000, 'completion_tokens' => 500],
            ],
            'tags' => ['agent' => Agents::GuidelineRecommender->value],
        ];
    }

    /**
     * A case whose guideline recommendations carry mined reviewer labels.
     *
     * @param  array<int, string>  $accepted
     * @param  array<int, string>  $rejected
     * @param  array<int, string>  $missed
     */
    public function withMinedLabels(array $accepted = [], array $rejected = [], array $missed = []): static
    {
        return $this->state(fn () => [
            'labels' => [
                'mined' => [
                    'accepted_guideline_ids' => $accepted,
                    'rejected_guideline_ids' => $rejected,
                    'missed_guideline_ids' => $missed,
                ],
            ],
        ]);
    }

    public function structuredOutput(): static
    {
        return $this->state(fn () => [
            'agent_id' => Agent::firstOrCreate(['name' => Agents::StructuredOutput->value])->id,
            'tags' => ['agent' => Agents::StructuredOutput->value],
        ]);
    }
}
