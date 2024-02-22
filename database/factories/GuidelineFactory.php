<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Criterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Guideline>
 */
class GuidelineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->numberBetween(1, 90),
            'name' => $this->faker->sentence,
            'criterion_id' => Criterion::factory(),
            'category_id' => Category::factory(),
            'notes' => $this->faker->paragraph,
        ];
    }
}
