<?php

namespace App\Services\AccessibilityContentParser\ActRules;

use App\Services\AccessibilityContentParser\ActRules\DataObjects\Rule;
use DOMElement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Yaml\Yaml;

abstract class ActRuleBase
{
    public function getMachineName(): string
    {
        $className = (new ReflectionClass($this))->getShortName();
        $slug = Str::of($className)->snake()->slug();

        // Put a dash before the hex hash at the end of the class name
        return preg_replace('/([0-9a-f]{6})$/', '-$1', $slug);
    }

    public function getRuleData(): array
    {
        $dataFile = Storage::get('act-rules-yaml/' . $this->getMachineName() . '.yaml');

        return Yaml::parse($dataFile);
    }

    public function getRule(): Rule
    {
        return Rule::fromYaml($this->getRuleData());
    }

    public function doesRuleApply($htmlContent): bool
    {
        $crawler = new Crawler($htmlContent);
        $elements = $this->findApplicableElements($crawler);

        foreach ($elements as $element) {
            if ($this->isElementIncludedInAccessibilityTree($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Confirm that the element and its ancestors are included in the accessibility tree.
     */
    protected function isElementIncludedInAccessibilityTree(DOMElement $element): bool
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

    abstract protected function findApplicableElements(Crawler $crawler): Crawler;
}
