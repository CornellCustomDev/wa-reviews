<?php

namespace App\Console\Commands;

use App\Models\SiaRule;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class ParseAlfaRulesScrape extends Command
{
    protected $signature = 'app:parse-alfa-rules-scrape';

    protected $description = 'Imports Siteimprove Alfa rules from a web scrape of https://alfa.siteimprove.com/rules';

    public function handle()
    {
        $html = file_get_contents('https://alfa.siteimprove.com/rules');
        //$html = Storage::get('alfa-rules-scrape.html');

        $crawler = new Crawler($html);

        // Clear the table
        SiaRule::truncate();

        // Select all rule cards in the "main" element
        $rules = $crawler->filter('main a')->each(function (Crawler $node) {
            $alfa_id = $node->filter('span')->text();

            $span = $node->filter('span')->outerHtml();
            $nameHtml = Str::of($node->filter('p.h5')->html())->replace($span, '')->trim();
            $nameText = Str::of($node->filter('p.h5')->text())->replace($alfa_id, '')->trim();

            $rule = [
                'id' => Str::replace('SIA-R', '', $alfa_id),
                'alfa' => Str::lower($alfa_id),
                'name' => $nameText,
                'name_html' => $nameHtml,
            ];

            // Store the SiaRule in the database
            SiaRule::updateOrCreate(['id' => $rule['id']], $rule);

            return $rule;
        });

        // Display the rules in a table
        $this->table(['ID', 'Number', 'Name', 'Name HTML'], $rules);

        $this->info('Rules successfully parsed and displayed.');
    }
}
