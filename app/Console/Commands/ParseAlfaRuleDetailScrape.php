<?php

namespace App\Console\Commands;

use App\Models\Criterion;
use App\Models\SiaRule;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class ParseAlfaRuleDetailScrape extends Command
{
    protected $signature = 'app:parse-alfa-rule-detail-scrape';
    protected $description = 'Scrapes each rule detail page from https://alfa.siteimprove.com/rules and stores the details in the sia_rules table';

    public function handle()
    {
        $siaRules = SiaRule::all();

        // Truncate sia_rule_criterion table
        \DB::table('sia_rule_criterion')->truncate();

        $this->info('Scraping '.count($siaRules).' rule details...');

        $this->withProgressBar($siaRules, function (SiaRule $rule) use (&$levelsFound) {
//            try {
//                $html = file_get_contents('https://alfa.siteimprove.com/rules/' . $rule->alfa);
//            } catch (\Exception $e) {
//                $this->error('Failed to scrape rule ' . $rule->alfa . ': ' . $e->getMessage());
//                return;
//            }
//            // Confirm the rule is not empty
//            if (empty($html)) {
//                $this->error('Failed to scrape rule ' . $rule->alfa . ': Empty response');
//                return;
//            }
//            // Wait 0.5 seconds to avoid rate limiting
//            usleep(500000);
//
//            $crawler = new Crawler($html);
//            $rule->rule_html = $crawler->filter('main div div div')->html();
//            // Strip the text from the class name suffixes that are added by Siteimprove build system
//            $rule->rule_html = preg_replace('/([a-zA-Z])(__[0-9a-zA-Z_]{5})([\s\"])/', '$1$3', $rule->rule_html);
//            $rule->save();

            $crawler = new Crawler($rule->rule_html);

            // Find the list items for rule levels
            $conformanceRules = $crawler->filter('.rules_level');
            if ($conformanceRules->count() === 0) {
                $this->error('No conformance rules found for ' . $rule->alfa);
                return;
            }

            $criteriaNodes = $conformanceRules->closest('ul')->children();

            //  <li>
            //      <span title="Level A">
            //          <svg viewBox="0 0 40 20" class="icon_icon rules_level rules_level-a" aria-hidden="true">
            //              ...
            //          </svg>
            //      </span>
            //      <a href="https://www.w3.org/TR/WCAG2/#link-purpose-in-context">2.4.4<!-- --> <!-- -->Link Purpose (In Context)</a>
            //  </li>

            $criteriaNodes->each(function ($node) use ($rule, &$levelsFound) {
                $levelTitle = $node->filter('span')->attr('title');

                $criteria = $node->filter('a');

                $criteriaNumber = Str::before($criteria?->text(), ' ');
                $criteriaName = Str::after($criteria?->text(), ' ');
                $criteriaHref = $criteria->attr('href');

                // Find the criterion that has the same number as the criteriaNumber
                $criterion = Criterion::firstWhere('number', $criteriaNumber);

                // If the criterion doesn't exist, add it
                if (!$criterion) {
                    $criterion = Criterion::create([
                        'number' => $criteriaNumber,
                        'name' => $criteriaName,
                        'level' => match($levelTitle) {
                            'Level AAA' => 'A',
                            'ARIA authoring practice' => 'ARIA',
                        }
                    ]);
                }

                // Put the data into the sia_rule_criterion table (there is no model)
                $data = [
                    //'rule' => $rule->alfa,
                    'level' => $levelTitle,
                    'criterion' => $criteriaNumber,
                    'name' => $criteriaName,
                    'link' => $criteriaHref,
                ];

                $rule->criteria()->attach($criterion->id, $data);
            });

        });
        $this->output->newLine();

        $this->info('Rule details scraped and stored.');
    }

}
