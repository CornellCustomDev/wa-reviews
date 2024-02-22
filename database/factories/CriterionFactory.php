<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Criterion>
 */
class CriterionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words,
            'number' => $this->faker->numberBetween(1, 4).'.'.$this->faker->numberBetween(1, 5).'.'.$this->faker->numberBetween(1, 13),
            'level' => $this->faker->randomElement(['A', 'AA', 'AAA']),
        ];
    }
}
