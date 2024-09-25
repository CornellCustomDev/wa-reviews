<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\ActRuleBase;
use Symfony\Component\DomCrawler\Crawler;

class FormFieldNonEmptyAccessibleNamee086e5 extends ActRuleBase
{
    protected function findApplicableElements(Crawler $crawler): Crawler
    {
        // Define the semantic roles specified in the rule applicability.
        $roles = [
            'checkbox',
            'combobox',
            'listbox',
            'menuitemcheckbox',
            'menuitemradio',
            'radio',
            'searchbox',
            'slider',
            'spinbutton',
            'switch',
            'textbox',
        ];

        // Build the selector for elements with the specified roles.
        $roleSelector = implode(',', array_map(function ($role) {
            return sprintf('[role="%s"]', $role);
        }, $roles));

        // Define selectors for native elements that implicitly have the specified roles.
        $nativeSelectors = [
            'input[type="checkbox"]',   // checkbox
            "select:not([role='none'][disabled])", // combobox, as long as it's not disabled
            'datalist',                 // listbox
            'input[type="radio"]',      // radio
            'input[type="search"]',     // searchbox
            'input[type="range"]',      // slider
            'input[type="number"]',     // spinbutton
            'input[type="text"]',       // textbox
            'input[type="email"]',      // textbox
            'input[type="tel"]',        // textbox
            'input[type="url"]',        // textbox
            'input:not([type])',        // not specified
            'textarea',                 // textbox
            // Add any other native elements as needed.
        ];

        // Combine role-based selectors and native element selectors.
        $selectors = array_merge([$roleSelector], $nativeSelectors);
        $combinedSelector = implode(',', $selectors);

        // Filter elements based on the combined selector and accessibility tree inclusion.
        return $crawler->filter($combinedSelector)->reduce(function (Crawler $node) {
            return $this->isElementIncludedInAccessibilityTree($node->getNode(0));
        });
    }

}
