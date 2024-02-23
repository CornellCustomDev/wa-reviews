<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Guideline;
use App\Models\Project;
use App\Models\Review;
use App\Models\ReviewItem;
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
            $reviews = Review::factory(rand(0, 10))->create([
                'project_id' => $project->id,
            ]);

            // Add 1 - 3 ReviewItems for each review, using a random guideline for each ReviewItem
            foreach ($reviews as $review) {
                ReviewItem::factory(rand(1, 3))
                    ->sequence(fn ($sequence) => [
                        'review_id' => $review->id,
                        'guideline_id' => $guidelines->random()->id,
                    ])
                    ->create();
            }
        }
    }
}
