<?php

namespace Database\Factories;

use App\Enums\Assessment;
use App\Models\Guideline;
use App\Models\Project;
use App\Models\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'scope_id' => Scope::factory(),
            'guideline_id' => fn () => Guideline::inRandomOrder()->value('id'),
            'assessment' => $this->faker->randomElement(Assessment::cases()),
            'target' => $this->faker->url.' '.$this->faker->words(asText: true),
            'description' => $this->faker->sentence,
            'recommendation' => $this->faker->sentence,
        ];
    }
}
