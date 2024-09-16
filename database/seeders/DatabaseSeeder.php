<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Guideline;
use App\Models\Project;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Criterion;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);
        $this->call(GuidelineSeeder::class);
        $guidelines = Guideline::all();

        $projects = Project::factory(10)->create();

        // Add between 0 and 10 reviews to each project
        foreach ($projects as $project) {
            $issues = Issue::factory(rand(0, 10))->create([
                'project_id' => $project->id,
            ]);

            // Add 1 - 3 Items for each review, using a random guideline for each Item
            foreach ($issues as $issue) {
                Item::factory(rand(1, 3))
                    ->sequence(fn ($sequence) => [
                        'issue_id' => $issue->id,
                        'guideline_id' => $guidelines->random()->id,
                    ])
                    ->create();
            }
        }
    }
}
