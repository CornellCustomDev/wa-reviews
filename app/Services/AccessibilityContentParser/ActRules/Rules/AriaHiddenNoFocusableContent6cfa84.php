<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\RuleRunner;
use Symfony\Component\DomCrawler\Crawler;

class AriaHiddenNoFocusableContent6cfa84 extends RuleRunner
{
    protected function findApplicableElements(Crawler $crawler): Crawler
    {
        return $crawler->filterXpath("//*[@aria-hidden='true']");
    }

}
