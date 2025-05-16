<?php

namespace App\Services\AccessibilityAnalyzer;

use App\Services\CornellAI\OpenAIChatService;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class AccessibilityAnalyzerService
{
    public string $response;

    /**
     * @throws Exception
     */
    public function retrieveHtml(string $url): ?string
    {
        try {
            $response = Http::get($url);
        } catch (Exception $e) {
            throw new Exception("Error response retrieving '$url':  " . $e->getMessage());
        }

        if ($response->failed()) {
            throw new Exception('Failed to retrieve the URL: ' . $url);
        }

        if (!$response->ok()) {
            return null;
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

    public function getPageContent(string $pageUrl, bool $bustCache = false): ?string
    {
        if (empty($pageUrl)) {
            return null;
        }

        $cacheKey = 'page_body_' . md5($pageUrl);
        if ($bustCache) {
            cache()->forget($cacheKey);
        }
        return cache()->remember($cacheKey, 3600, function () use ($pageUrl) {
            try {
                $html = $this->retrieveHtml($pageUrl);
                $crawler = $this->parseDom($html);
                return $crawler->html();
            } catch (Exception $e) {
                return null;
            }
        });
    }

    public function getApplicableRules($content = null): Collection
    {
        $rules = self::getAllRuleRunners();

        return $rules->filter(fn(RuleRunner $rule) => $rule->doesRuleApply($content))
            ->map(fn(RuleRunner $rule) => $rule->getModel());
    }

    public static function getAllRuleRunnerClassNames()
    {
        $actRuleClassNames = collect(scandir(__DIR__ . '/ActRules'))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php')
            ->map(fn($file) => 'App\Services\AccessibilityAnalyzer\ActRules\\' . pathinfo($file, PATHINFO_FILENAME));

        $guidelineRuleClassNames = collect(scandir(__DIR__ . '/GuidelineRules'))
            ->filter(fn($file) => pathinfo($file, PATHINFO_EXTENSION) === 'php')
            ->map(fn($file) => 'App\Services\AccessibilityAnalyzer\GuidelineRules\\' . pathinfo($file, PATHINFO_FILENAME));

        return $actRuleClassNames->merge($guidelineRuleClassNames);
    }


    public function getApplicableRuleRunners(mixed $content): Collection
    {
        return self::getAllRuleRunners()
            ->filter(fn(RuleRunner $rule) => $rule->doesRuleApply($content));
    }

    public function getNodesWithApplicableRules($content): array
    {
        $rules = self::getAllRuleRunners();

        $nodes = [];
        /** @var RuleRunner $rule */
        foreach ($rules as $rule) {
            $ruleNodes = $rule->getNodesWhereRuleApplies($content);
            if (!empty($ruleNodes)) {
                $combinedSelectors = collect($ruleNodes)->map(fn($node) => $node['css_selector'])->join(',');
                $nodes[$rule->getModel()->getMachineName()] = [
                    'cssSelectors' => $combinedSelectors,
                    'nodes' => $ruleNodes,
                ];
            }
        }

        return $nodes;
    }

    public function getApplicableScopeRuleNodes($content): array
    {
        $rules = self::getAllRuleRunners();

        $scopeRules = [];
        /** @var RuleRunner $rule */
        foreach ($rules as $rule) {
            if ($ruleNodes = $rule->getNodesWhereRuleApplies($content)) {
                foreach ($ruleNodes as $node) {
                    $scopeRules[] = [
                        'ruleRunner' => $rule,
                        'node_info' => $node,
                    ];
                }
            }
        }

        return $scopeRules;
    }

    public function reviewRuleWithAI(RuleRunner $ruleRunner, Collection $scopeRules, string $html): array
    {
        $ruleIds = $scopeRules->pluck('id')->toArray();
        $cssSelectors = $scopeRules->pluck('css_selector')->toArray();

        $nodes = $this->findNodes($html, $cssSelectors);
        $prompt = $this->getNodesPrompt($ruleRunner, $nodes, $html);
        $chat = app(OpenAIChatService::class);
        $chat->setPrompt($prompt);
        $chat->send();

        // TODO Push this into a data object so we have a contract for the response format
        $response = json_decode($chat->getLastAiResponse());

        $this->response = json_encode($response, JSON_PRETTY_PRINT);

        $results = [];
        if (isset($response->elements)) {
            foreach ($response->elements as $element) {
                $id = $ruleIds[$element->elementIndex];
                $selector = $cssSelectors[$element->elementIndex];
                $results[$id] = [
                    'machine_name' => $ruleRunner->getMachineName(),
                    'css_selector' => $selector,
                    'description' => $element->elementDescription,
                    'assessment' => $element->assessment,
                    'reasoning' => $element->reasoning,
                ];
            }
        }

        return $results;
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
            'div' => 'Division',
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

            $tagName = $domElement->nodeName;

            $identifier = null;
            $descriptor = null;

            // Check for ARIA role
            $role = $node->attr('role');
            if ($role && isset($ariaLandmarks[$role])) {
                $identifier = $role;
                $descriptor = $ariaLandmarks[$role];
            }

            // Check for semantic element
            if (!$descriptor) {
                if (isset($semanticElements[$tagName])) {
                    $identifier = $tagName;
                    $descriptor = $semanticElements[$tagName];
                }
            }

            // If no descriptor found, skip element
            if (!$descriptor) {
                continue;
            }

            // Add to result
            $result[$identifier] = [
                'name' => $descriptor,
                'css_selector' => RuleRunner::getCssSelector($domElement),
                'element' => $domElement, // DOMElement or DOMNode
            ];
        }

        return $result;
    }

    public static function getAllRuleRunners(): Collection
    {
        return self::getAllRuleRunnerClassNames()->map(fn($className) => new $className);
    }

    public function getNodesPrompt(RuleRunner $ruleRunner, array $nodes, string $html): string
    {
        $ruleDescription = $ruleRunner->getAiPromptDescription();

        // Prepare HTML for all elements
        $elementsText = "";
        foreach ($nodes as $index => $node) {
            // Convert the element to HTML
            $dom = new \DOMDocument();
            $dom->appendChild($dom->importNode($node, true));
            $elementHtml = $dom->saveHTML();
            $elementsText .= "Element (elementIndex:$index):\n{$elementHtml}\n\n";
        }

        // Build the prompt
        $prompt = <<<PROMPT
You are an AI assistant specialized in web accessibility compliance.

Below is an accessibility rule:

{$ruleDescription}

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
      "elementDescription": "Where this element is on the page",
      "assessment": "pass" | "fail" | "ambiguous",
      "reasoning": "Your detailed reasoning here."
    },
    {
      "elementIndex": 1,
      "elementDescription": "Where this element is on the page",
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
