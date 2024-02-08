<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Project;
use App\Models\Review;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $projects = Project::factory(10)->create();

        // Add between 0 and 10 reviews to each project
        foreach ($projects as $project) {
            Review::factory(rand(0, 10))->create([
                'project_id' => $project->id,
            ]);
        }
    }
}
