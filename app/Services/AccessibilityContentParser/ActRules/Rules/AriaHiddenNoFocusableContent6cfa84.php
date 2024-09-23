<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\ActRuleBase;
use Symfony\Component\DomCrawler\Crawler;

class AriaHiddenNoFocusableContent6cfa84 extends ActRuleBase
{
    protected function findApplicableElements(Crawler $crawler): Crawler
    {
        return $crawler->filterXpath("//*[@aria-hidden='true']");
    }

    protected function hasApplicableElements(Crawler $crawler): bool
    {
        return $this->findApplicableElements($crawler)->count() > 0;
    }
}
