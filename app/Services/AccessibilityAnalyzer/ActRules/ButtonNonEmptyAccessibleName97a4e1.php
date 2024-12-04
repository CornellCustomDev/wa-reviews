<?php

namespace App\Services\AccessibilityAnalyzer\ActRules;

use App\Services\AccessibilityAnalyzer\ActRuleRunner;
use Symfony\Component\DomCrawler\Crawler;

class ButtonNonEmptyAccessibleName97a4e1 extends ActRuleRunner
{
    public function findApplicableElements(Crawler $crawler): Crawler
    {
        return $crawler->filterXPath("
            //button[not(@type='image') and not(@role='link') and not(@role='none' and @disabled)]
            | //input[
                (@type='button' or @type='submit' or @type='reset')
                and not(@type='image')
              ]
            | //*[@role='button']
        ")->reduce(fn (Crawler $node) => $this->isElementIncludedInAccessibilityTree($node->getNode(0)));
    }
}
