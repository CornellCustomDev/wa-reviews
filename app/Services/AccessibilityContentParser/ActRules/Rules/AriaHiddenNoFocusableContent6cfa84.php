<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\RuleRunnerBase;
use Symfony\Component\DomCrawler\Crawler;

class AriaHiddenNoFocusableContent6cfa84 extends RuleRunnerBase
{
    protected function findApplicableElements(Crawler $crawler): Crawler
    {
        return $crawler->filterXpath("//*[@aria-hidden='true']");
    }

}
