<?php

namespace App\Services\AccessibilityContentParser\ActRules\Rules;

use App\Services\AccessibilityContentParser\ActRules\ActRuleBase;
use Symfony\Component\DomCrawler\Crawler;

class AutocompleteValidValue73f2c2 extends ActRuleBase
{
    public function findApplicableElements(Crawler $crawler): Crawler
    {
        // First, select all input, select, and textarea elements with an autocomplete attribute
        $elements = $crawler->filter('input[autocomplete], select[autocomplete], textarea[autocomplete]');

        // Now, filter the elements to only those that meet the applicability
        $applicableElements = $elements->reduce(function (Crawler $node, $i) {
            // Get the autocomplete attribute value
            $autocomplete = $node->attr('autocomplete');
            if ($autocomplete === null) {
                // Should not happen, since we already filtered on autocomplete attribute
                return false;
            }

            // Trim ASCII whitespace from the autocomplete value
            $trimmed = trim($autocomplete, "\x09\x0A\x0C\x0D\x20");

            // Exclude elements where the autocomplete value is empty or only whitespace
            if ($trimmed === '') {
                // The autocomplete value is empty or only whitespace, exclude this element
                return false;
            }

            // Now check for exception conditions

            // 1. Toggle: autocomplete attribute consists of a single token that is an ASCII case-insensitive match for 'off' or 'on'
            $tokens = preg_split('/[\x09\x0A\x0C\x0D\x20]+/', $trimmed);
            if (count($tokens) === 1) {
                $token = strtolower($tokens[0]);
                if ($token === 'off' || $token === 'on') {
                    // The autocomplete attribute is 'off' or 'on', exclude this element
                    return false;
                }
            }

            // 2. Disabled: the element is a disabled element
            if ($node->attr('disabled') !== null) {
                // The element has the 'disabled' attribute, exclude this element
                return false;
            }

            // Also check if 'aria-disabled' is set to 'true'
            $ariaDisabled = $node->attr('aria-disabled');
            if ($ariaDisabled !== null && strtolower($ariaDisabled) === 'true') {
                // The element has 'aria-disabled="true"', exclude this element
                return false;
            }

            // 3. Fixed value: the element is an input element with a type attribute value of 'button', 'checkbox', 'file', 'image', 'radio', 'reset', or 'submit'
            if ($node->nodeName() === 'input') {
                $type = strtolower($node->attr('type') ?: 'text');
                if (in_array($type, ['button', 'checkbox', 'file', 'image', 'radio', 'reset', 'submit'])) {
                    // The input element has a type that is a fixed value, exclude this element
                    return false;
                }
            }

            // 4. Hidden: the element is not visible, and not included in the accessibility tree
            // Check for 'input' elements with type='hidden'
            if ($node->nodeName() === 'input') {
                $type = strtolower($node->attr('type') ?: 'text');
                if ($type === 'hidden') {
                    // The input element is type='hidden', exclude this element
                    return false;
                }
            }

            // Also check for 'style' attribute with 'display:none' or 'visibility:hidden'
            $style = $node->attr('style');
            if ($style !== null) {
                $styleLower = strtolower($style);
                if (strpos($styleLower, 'display:none') !== false || strpos($styleLower, 'visibility:hidden') !== false) {
                    // The element is hidden via style, exclude this element
                    return false;
                }
            }

            // Also check for 'hidden' attribute
            if ($node->attr('hidden') !== null) {
                // The element has the 'hidden' attribute, exclude this element
                return false;
            }

            // 5. Static: the element is not part of sequential focus navigation and has a semantic role that is not a widget role.

            // Check if the element is focusable
            $focusable = true;
            // An element is not focusable if it has 'tabindex="-1"'
            $tabindex = $node->attr('tabindex');
            if ($tabindex !== null && $tabindex === '-1') {
                $focusable = false;
            }

            // Check the semantic role
            $role = $node->attr('role');
            $widgetRoles = [
                'alert',
                'alertdialog',
                'button',
                'checkbox',
                'dialog',
                'gridcell',
                'link',
                'log',
                'marquee',
                'menuitem',
                'menuitemcheckbox',
                'menuitemradio',
                'option',
                'progressbar',
                'radio',
                'scrollbar',
                'searchbox',
                'slider',
                'spinbutton',
                'status',
                'tab',
                'tabpanel',
                'textbox',
                'timer',
                'tooltip',
                'treeitem',
            ];

            if (!$focusable) {
                // The element is not focusable
                if ($role !== null) {
                    if (!in_array(strtolower($role), $widgetRoles)) {
                        // The element has a role that is not a widget role, exclude it
                        return false;
                    }
                } else {
                    // Determine implicit role
                    $nodeName = $node->nodeName();
                    if ($nodeName === 'input') {
                        $type = strtolower($node->attr('type') ?: 'text');
                        if (!in_array($type, ['button', 'submit', 'reset', 'checkbox', 'radio', 'text', 'search', 'url', 'tel', 'email', 'password'])) {
                            // Input type without a widget role, exclude the element
                            return false;
                        }
                    } elseif ($nodeName === 'select' || $nodeName === 'textarea') {
                        // 'select' and 'textarea' elements have widget roles
                    } else {
                        // Unlikely, but include the element
                    }
                }
            }

            // If none of the exceptions apply, include this element
            return true;
        });

        // Return the applicable elements
        return $applicableElements;
    }

}
