<?php

namespace Database\Factories;

use App\Enums\ChatProfile;
use App\Enums\EvalRunStatus;
use App\Models\EvalRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvalRun>
 */
class EvalRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => $this->faker->words(2, true),
            'dataset_version' => 'test-1',
            'status' => EvalRunStatus::Pending,
            'repeats' => 2,
            'models' => [
                'provider' => 'cornell',
                'profiles' => [
                    ChatProfile::Chat->value => 'openai.gpt-4.1-mini',
                    ChatProfile::Task->value => 'openai.gpt-4o-mini',
                ],
            ],
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => EvalRunStatus::Completed,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now(),
        ]);
    }

    /**
     * @param  array<string, string>  $profiles
     */
    public function withModels(array $profiles): static
    {
        return $this->state(fn (array $attributes) => [
            'models' => array_merge($attributes['models'], ['profiles' => $profiles]),
        ]);
    }
}
