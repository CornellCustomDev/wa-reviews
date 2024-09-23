<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Scope;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scope>
 */
class ScopeFactory extends Factory
{
    protected $model = Scope::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => $this->faker->words(asText: true),
            'url' => $this->faker->url(),
            'notes' => $this->faker->paragraph,
//            'comments' => $this->faker->randomElements(['comment1', 'comment2', 'comment3'], 2),
        ];
    }
}
