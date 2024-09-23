<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\ActRuleBase;
use Symfony\Component\DomCrawler\Crawler;

class ButtonNonEmptyAccessibleName97a4e1 extends ActRuleBase
{
    public function findApplicableElements(Crawler $crawler): Crawler
    {
        return $crawler->filterXPath("
            //button[not(@type='image') and not(@role='link')]
            | //input[
                (@type='button' or @type='submit' or @type='reset')
                and not(@type='image')
              ]
            | //*[@role='button']
        ");
    }
}
