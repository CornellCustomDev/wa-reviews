<?php

namespace App\Console\Commands;

use App\Models\ActRule;
use App\Models\Guideline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;


class ParseActRuleGuidelineMapping extends Command
{
    protected $signature = 'app:parse-act-rule-guideline-mapping';

    public function handle()
    {
        // Empty the pivot table
        \DB::table('act_rule_guideline')->truncate();

        $mappingsData = Storage::get('ACT Rule to Guideline Mapping - Checklist.csv');

        // Convert from CSV to array
        $lines = explode(PHP_EOL, $mappingsData);
        $header = str_getcsv(array_shift($lines));
        $mappings = [];

        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $mappings[] = array_combine($header, str_getcsv($line));
            }
        }

        // Report how many lines found
        $this->info('Found ' . count($mappings) . ' mappings');

        $count = 0;
        collect($mappings)
            ->filter(fn($mapping) => !empty($mapping['act_rule_id']))
            ->each(function ($mapping) use (&$count) {
                $actRule = ActRule::find($mapping['act_rule_id']);
                $guideline = Guideline::find($mapping['guideline_number']);

                if (empty($actRule) || empty($guideline)) {
                    // Report a warning
                    $this->warn('Could not find act rule or guideline for mapping: ' . json_encode($mapping));
                    return;
                }

                $actRule->guidelines()->attach($guideline);
                $count++;
            });

        $this->info('Added ' . $count . ' relationships.');
    }
}
