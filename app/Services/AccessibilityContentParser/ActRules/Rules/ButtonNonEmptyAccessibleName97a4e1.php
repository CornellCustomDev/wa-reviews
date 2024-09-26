<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\RuleRunnerBase;
use Symfony\Component\DomCrawler\Crawler;

class ButtonNonEmptyAccessibleName97a4e1 extends RuleRunnerBase
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
        ")->reduce(function (Crawler $node) {
            return $this->isElementIncludedInAccessibilityTree($node->getNode(0));
        });
    }
}
