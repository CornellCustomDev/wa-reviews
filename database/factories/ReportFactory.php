<?php

namespace Database\Factories;

use App\Enums\ReportType;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'type' => $this->faker->randomElement(ReportType::cases())->value,
        ];
    }
}
