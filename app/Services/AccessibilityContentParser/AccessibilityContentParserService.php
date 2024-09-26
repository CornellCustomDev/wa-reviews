<?php

namespace App\Services\AccessibilityContentParser;

use App\Models\ActRule;
use App\Services\AccessibilityContentParser\ActRules\RuleRunner;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class AccessibilityContentParserService
{
    /**
     * @throws Exception
     */
    public function retrieveHtml(string $url): string
    {
        try {
            $response = Http::get($url);
        } catch (Exception $e) {
            throw new Exception("Error response retrieving '$url':  " . $e->getMessage());
        }

        if ($response->failed()) {
            throw new Exception('Failed to retrieve the URL: ' . $url);
        }

        return $response->body();
    }

    public function parseDom(string $html): Crawler
    {
        return new Crawler($html);
    }

    public function findNodes(string $html, array $cssSelectors): array
    {
        $nodes = [];
        foreach ($cssSelectors as $cssSelector) {
            $crawler = $this->parseDom($html)->filter($cssSelector);
            if ($crawler->count() > 0) {
                $nodes[$cssSelector] = $crawler->getNode(0);
            }
        }

        return $nodes;
    }

    public function getPageContent(string $pageUrl)
    {
        $cacheKey = 'page_body_' . md5($pageUrl);
        return cache()->remember($cacheKey, 3600, function () use ($pageUrl) {
            $html = $this->retrieveHtml($pageUrl);
            $crawler = $this->parseDom($html);
            return $crawler->html();
        });
    }

    public function getApplicableRules($content = null): Collection
    {
        $rules = self::getAllRules();

        return $rules->filter(fn(RuleRunner $rule) => $rule->doesRuleApply($content))
            ->map(fn(RuleRunner $rule) => $rule->getActRule());
    }

    public function getNodesWithApplicableRules($content): array
    {
        $rules = self::getAllRules();

        $nodes = [];
        /** @var RuleRunner $rule */
        foreach ($rules as $rule) {
            $ruleNodes = $rule->getNodesWhereRuleApplies($content);
            if (!empty($ruleNodes)) {
                $combinedSelectors = collect($ruleNodes)->map(fn($node) => $node['css_selector'])->join(',');
                $nodes[$rule->getActRule()->getMachineName()] = [
                    'cssSelectors' => $combinedSelectors,
                    'nodes' => $ruleNodes,
                ];
            }
        }

        return $nodes;
    }

    public function extractMajorSections(string $html): array
    {
        // Initialize the DomCrawler with the HTML content
        $crawler = new Crawler($html);

        // Define ARIA landmarks and their descriptors
        $ariaLandmarks = [
            'banner' => 'Banner',
            'navigation' => 'Navigation',
            'main' => 'Main Content',
            'complementary' => 'Complementary',
            'contentinfo' => 'Footer',
            'search' => 'Search',
            'form' => 'Form',
            'region' => 'Region',
        ];

        // Define HTML5 semantic elements and their descriptors
        $semanticElements = [
            'header' => 'Header',
            'nav' => 'Navigation',
            'main' => 'Main Content',
            'aside' => 'Aside',
            'footer' => 'Footer',
            'section' => 'Section',
            'article' => 'Article',
            'form' => 'Form',
        ];

        // Build XPath expressions for ARIA landmarks
        $ariaXPathExpressions = [];
        foreach (array_keys($ariaLandmarks) as $role) {
            $ariaXPathExpressions[] = "//*[@role='$role']";
        }
        $ariaXPath = implode(' | ', $ariaXPathExpressions);

        // Build XPath expressions for semantic elements
        $semanticXPathExpressions = [];
        foreach (array_keys($semanticElements) as $element) {
            $semanticXPathExpressions[] = "//{$element}";
        }
        $semanticXPath = implode(' | ', $semanticXPathExpressions);

        // Combine both XPath expressions to get all relevant elements
        $combinedXPath = $ariaXPath . ' | ' . $semanticXPath;

        // Find all matching elements in document order
        $elements = $crawler->filterXPath($combinedXPath);

        $result = [];

        foreach ($elements as $domElement) {
            $node = new Crawler($domElement);

            // Initialize descriptor
            $descriptor = null;

            // Check for ARIA role
            $role = $node->attr('role');
            if ($role && isset($ariaLandmarks[$role])) {
                $descriptor = $ariaLandmarks[$role];
            }

            // Check for semantic element
            if (!$descriptor) {
                $tagName = $domElement->nodeName;
                if (isset($semanticElements[$tagName])) {
                    $descriptor = $semanticElements[$tagName];
                }
            }

            // If no descriptor found, skip element
            if (!$descriptor) {
                continue;
            }

            $id = $node->attr('id');

            // Add to result
            $result[$id ?? $descriptor] = [
                'name' => $descriptor,
                'css_selector' => RuleRunner::getCssSelector($domElement),
                'element' => $domElement, // DOMElement or DOMNode
            ];
        }

        return $result;
    }

    public static function getAllRules(): Collection
    {
        return collect(scandir(__DIR__.'/ActRules/Rules'))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php')
            ->map(fn($file) => pathinfo($file, PATHINFO_FILENAME))
            ->map(function ($className) {
                $fullyQualifiedClassName = 'App\Services\AccessibilityContentParser\ActRules\Rules\\' . $className;
                return new $fullyQualifiedClassName;
            });
    }

    public function getNodesPrompt(ActRule $actRule, array $nodes, string $html): string
    {
        // Extract rule details
        $ruleDescription = $actRule->getLongDescription();
        $passingTestCases = $actRule->getPassingTestCases();
        $failingTestCases = $actRule->getFailingTestCases();

        // Prepare passing test cases
        $passingCasesText = "";
        foreach ($passingTestCases as $testCase) {
            $passingCasesText .= "Description: " . $testCase['description'] . "\n";
            $passingCasesText .= "HTML:\n" . $testCase['html'] . "\n\n";
        }

        // Prepare failing test cases
        $failingCasesText = "";
        foreach ($failingTestCases as $testCase) {
            $failingCasesText .= "Description: " . $testCase['description'] . "\n";
            $failingCasesText .= "HTML:\n" . $testCase['html'] . "\n\n";
        }

        // Prepare HTML for all elements
        $elementsText = "";
        foreach ($nodes as $index => $node) {
            // Convert the element to HTML
            $dom = new \DOMDocument();
            $dom->appendChild($dom->importNode($node, true));
            $elementHtml = $dom->saveHTML();
            $elementsText .= "Element (index:$index):\n{$elementHtml}\n\n";
        }

        // Build the prompt
        $prompt = <<<PROMPT
You are an AI assistant specialized in web accessibility compliance.

Below is an accessibility rule:

{$ruleDescription}

Passing Test Cases:
{$passingCasesText}

Failing Test Cases:
{$failingCasesText}

Here are multiple HTML elements from a web page:

{$elementsText}

The entire web page HTML follows below to help address element ancestry and context.

Based on the rule and the test cases, determine whether each element matches a passing test case, a failing test case, or is ambiguous. For each element, provide your reasoning and assessment.

Output Formatting:
  - All responses should be formatted as a JSON array, not markdown.
  - Each element's assessment as a separate item in the "elements" array:

{
  "elements": [
    {
      "elementIndex": 0,
      "assessment": "pass" | "fail" | "ambiguous",
      "reasoning": "Your detailed reasoning here."
    },
    {
      "elementIndex": 1,
      "assessment": "pass" | "fail" | "ambiguous",
      "reasoning": "Your detailed reasoning here."
    }
    // ... for each element
  ]
}

Web Page HTML:
PROMPT;

        return $prompt.$html;
    }


}
