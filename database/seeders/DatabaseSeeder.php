<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Guideline;
use App\Models\Project;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Scope;
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

        foreach ($projects as $project) {
            // Add between 3 and 5 scopes to each project
            $scopes = Scope::factory(rand(3, 5))->create([
                'project_id' => $project->id,
            ]);

            foreach ($scopes as $scope) {
                // Add between 0 and 3 issues to each scope in the project
                $issues = Issue::factory(rand(0, 3))->create([
                    'project_id' => $project->id,
                    'scope_id' => $scope->id,
                ]);
                // Add 1 - 3 Items for each issue, using a random guideline for each Item
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
}
