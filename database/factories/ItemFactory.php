<?php

namespace Database\Factories;

use App\Enums\Assessment;
use App\Enums\TestingMethod;
use App\Models\Guideline;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'issue_id' => Issue::factory(),
            'guideline_id' => Guideline::factory(),
            'assessment' => $this->faker->randomElement(Assessment::values()),
            'target' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'testing_method' => $this->faker->randomElement(TestingMethod::values()),
            'recommendation' => $this->faker->paragraph,
            'image_links' => [],
            'content_issue' => $this->faker->boolean,
        ];
    }
}
