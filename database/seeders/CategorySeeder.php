<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Color' => 'Tests about color contrast and the use of colors on the page.',
            'Content' => 'Tests about how content is written.',
            'Forms and Inputs' => 'Tests that verify the proper use of forms and inputs (buttons, selects, etc.)',
            'Images' => 'Tests where images are verified for appropriate use and accessibility features.',
            'Interaction' => 'Tests about how site functions behave when you interact with them.',
            'Keyboard' => 'Tests that require manual keyboard testing to verify (also covers Screen Readers)',
            'Motion' => 'Tests that must be checked when content moves or changes on its own.',
            'Multimedia' => 'Tests about video and audio.',
            'Resizing' => 'Tests about how the page behaves when resizing, zooming, or rotating the browser contents.',
            'Structure' => 'Tests about the theme and the appropriate use of HTML markup.',
        ];

        foreach ($categories as $name => $description) {
            Category::factory()->create([
                'name' => $name,
                'description' => $description,
            ]);
        }
    }
}
