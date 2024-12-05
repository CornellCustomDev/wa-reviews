<?php

namespace App\Console\Commands;

use App\Models\Criterion;
use App\Models\SiteimproveRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ParseSiteimproveCheckIds extends Command
{
    protected $signature = 'app:parse-siteimprove-check-ids';

    protected $description = 'Gets Siteimprove check ids and stores them in the model';

    public function handle()
    {
        $csv = Storage::readStream('siteimproveCheckIds.csv');

        // Clear the table
        SiteimproveRule::truncate();

        while ($data = fgetcsv($csv)) {
            // $data is [rule_id, category, issues]
            $category = trim($data[1]);
            // Find the category in the criteria table
            $criteria = Criterion::firstWhere('number', $category);
            SiteimproveRule::upsert([[
                'rule_id' => intval($data[0]),
                'category' => $category,
                'issues' => $data[2],
                'criterion_id' => $criteria?->id,
            ]], uniqueBy: ['rule_id', 'category', 'issues']);
        }

        $this->info('Stored ' . SiteimproveRule::count() . ' Siteimprove rules');
    }
}
