<?php

namespace App\Console\Commands;

use App\Models\Guideline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SplitGuidelines extends Command
{
    protected $signature = 'app:split-guidelines';

    public function handle()
    {
        $allGuidelines = Storage::get('guidelines-list.md');

        // Split the guidelines at the ## heading,
        $splitList = preg_split('/##\s+(\d+\.\s+[^\n]+)\n\n/', $allGuidelines, -1, PREG_SPLIT_DELIM_CAPTURE);

        // First element is the doc title
        array_shift($splitList);

        $this->info('Found ' . count($splitList) / 2 . ' guidelines');

        $guidelines = [];
        while ($splitList) {
            preg_match('/(\d+)\.\s+([^\n]+)/', array_shift($splitList), $matches);
            $guidelines[] = [
                'number' => $matches[1],
                'name' => $matches[2],
                'content' => array_shift($splitList),
            ];
        }

        $this->info('Parsed ' . count($guidelines) . ' guidelines');

        foreach ($guidelines as $guidelineData) {
            $guideline = Guideline::find($guidelineData['number']);
            $guideline->name = $guidelineData['name'];
            $guideline->notes = $guidelineData['content'];
            $guideline->save();
        }

        $this->info('Updated ' . count($guidelines) . ' guidelines');
    }
}
