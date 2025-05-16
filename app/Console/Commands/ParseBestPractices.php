<?php

namespace App\Console\Commands;

use App\Enums\GuidelineTools;
use App\Models\Category;
use App\Models\Guideline;
use Illuminate\Console\Command;

class ParseBestPractices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-best-practices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Split the UX best practices into their own guidelines';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $source = Guideline::find(90);

        // Split the guideline at the ## heading.
        $splitList = preg_split('/##\s+(Best Practice.\s+[^\n]+)\n\n/', $source->notes, -1, PREG_SPLIT_DELIM_CAPTURE);

        // First element is guideline 90 notes
        $notes = array_shift($splitList);

        $this->info('Found ' . count($splitList) / 2 . ' best practices');

        // Make sure category_id = 11 exists
        $category = Category::upsert([
            'id' => 11,
            'name' => 'Best Practice',
            'description' => 'Best practices which assure quality for accessible user experience.',
        ], 11);


        $number = 100;
        while ($splitList) {
            preg_match('/(Best Practice)\.\s+([^\n]+)/', array_shift($splitList), $matches);
            $tools = [GuidelineTools::BrowserExtension->value()];
            if ($number == 101) {
                $tools[] = GuidelineTools::Siteimprove;
            }
            Guideline::create([
                'id' => $number,
                'number' => $number,
                'criterion_id' => 7,
                'category_id' => 11,
                'name' => $matches[2],
                'notes' => array_shift($splitList),
                'tools' => $tools,
            ]);
            $number++;
        }

        $source->notes = $notes;
        $source->save();
    }
}
