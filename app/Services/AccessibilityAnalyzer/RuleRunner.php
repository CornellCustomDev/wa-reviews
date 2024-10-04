<?php

namespace App\Services\AccessibilityAnalyzer;

use DOMElement;
use Symfony\Component\DomCrawler\Crawler;

abstract class RuleRunner
{
    abstract public function getRuleType(): string;

    abstract public function getRuleId(): string;

    abstract public function getName(): string;

    abstract public function getMachineName(): string;

    abstract public function getGuidelineIds(): array;

    abstract public function getPassingTestCases(): array;
    abstract public function getFailingTestCases(): array;
    abstract public function getInapplicableTestCases(): array;

    abstract public function getAiPromptDescription(): string;

    public function doesRuleApply(string|DOMElement $content): bool
    {
        $crawler = new Crawler($content);

        return $this->findApplicableElements($crawler)->count() > 0;
    }

    abstract protected function findApplicableElements(Crawler $crawler): Crawler;

    public function getNodesWhereRuleApplies(string|DOMElement $content = null): array
    {
        $crawler = new Crawler($content);

        return $this->getApplicableNodes($crawler);
    }

    /**
     * Confirm that the element and its ancestors are included in the accessibility tree.
     */
    protected function isElementIncludedInAccessibilityTree(\DOMNode $element): bool
    {
        while ($element) {
            // aria-hidden='true'
            if ($element->hasAttribute('aria-hidden') && $element->getAttribute('aria-hidden') === 'true') {
                return false;
            }
            // hidden
            if ($element->hasAttribute('hidden')) {
                return false;
            }
            // style='display: none' or style='visibility: hidden'
            $styles = explode(';', $element->getAttribute('style'));
            if (preg_grep('/display:\s*none/', $styles)) {
                return false;
            }
            if (preg_grep('/visibility:\s*hidden/', $styles)) {
                return false;
            }

            // Recurse up the DOM tree
            $element = $element->parentNode instanceof DOMElement ? $element->parentNode : null;
        }

        return true;
    }


    protected function getApplicableNodes(Crawler $crawler): array
    {
        $elements = $this->findApplicableElements($crawler);

        $info = [];

        foreach ($elements as $element) {
            /** @var \DOMElement $element */
            $cssSelector = $this->getCssSelector($element);
            $description = $this->describeElement($element);

            $info[] = [
                'css_selector' => $cssSelector,
                'description' => $description,
                'line_number' => $element->getLineNo(),
            ];
        }

        return $info;
    }


    /**
     * Generates a unique CSS selector for a given DOMElement.
     */
    public static function getCssSelector(\DOMElement $element): string
    {
        $path = [];

        while ($element instanceof \DOMElement) {
            $selector = $element->tagName;

            // Use ID if available for uniqueness
            if ($element->hasAttribute('id')) {
                // Use attribute selector to escape special characters
                $selector .= '[id="'. $element->getAttribute('id') . '"]';
                $path[] = $selector;
                break; // IDs are unique in the document
            } else {
                // Add classes if available
                if ($element->hasAttribute('class')) {
                    $classes = explode(' ', trim($element->getAttribute('class')));
                    $classes = array_filter($classes); // Remove empty values
                    if (!empty($classes)) {
                        $selector .= '.' . implode('.', $classes);
                    }
                }

                // Determine the element's position among siblings
                $position = 1;
                $sibling = $element->previousSibling;
                while ($sibling) {
                    if ($sibling instanceof \DOMElement && $sibling->tagName === $element->tagName) {
                        $position++;
                    }
                    $sibling = $sibling->previousSibling;
                }

                $selector .= ':nth-of-type(' . $position . ')';
                $path[] = $selector;
                $element = $element->parentNode;
            }
        }

        return implode(' > ', array_reverse($path));
    }

    /**
     * Provides a brief description of the element.
     */
    public static function describeElement(\DOMElement $element): string
    {
        $tagName = $element->tagName;
        $attributes = [];

        if ($element->hasAttributes()) {
            foreach ($element->attributes as $attr) {
                $attributes[] = $attr->name . '="' . $attr->value . '"';
            }
        }

        $attrString = implode(' ', $attributes);
        $textContent = trim($element->textContent);
        if (strlen($textContent) > 50) {
            $textContent = substr($textContent, 0, 47) . '...';
        }

        return sprintf(
            '<%s %s>%s</%s>',
            $tagName,
            $attrString,
            $textContent,
            $tagName
        );
    }

}
