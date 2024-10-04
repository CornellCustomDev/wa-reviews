<?php

namespace App\Services\AccessibilityAnalyzer\ActRules;

use App\Services\AccessibilityAnalyzer\ActRuleRunner;
use Symfony\Component\DomCrawler\Crawler;

class AriaHiddenNoFocusableContent6cfa84 extends ActRuleRunner
{
    protected function findApplicableElements(Crawler $crawler): Crawler
    {
        return $crawler->filterXpath("//*[@aria-hidden='true']");
    }

}
