<?php

namespace App\Services\GuidelinesAnalyzer;

use App\AiAgents\Tools\AnalyzeIssueTool;
use App\AiAgents\Tools\StoreGuidelineMatchesTool;
use App\Enums\Agents;
use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Models\Agent;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Scope;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\Tools\AnalyzeIssue;
use App\Services\GuidelinesAnalyzer\Tools\FetchGuidelines;
use App\Services\GuidelinesAnalyzer\Tools\FetchGuidelinesDocument;
use App\Services\GuidelinesAnalyzer\Tools\FetchGuidelinesList;
use App\Services\GuidelinesAnalyzer\Tools\FetchIssuePageContent;
use App\Services\GuidelinesAnalyzer\Tools\StoreGuidelineMatches;
use Illuminate\Support\Str;

class GuidelinesAnalyzerService implements GuidelinesAnalyzerServiceInterface
{
    const array ITEM_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'reasoning' => [
                'type' => 'string',
                'description' => 'Brief explanation of: 1) how the guideline applies to the issue, 2) why it was assessed as a warning or failure, and 3) why the impact rating was chosen.',
            ],
            'number' => [
                'type' => 'integer',
                'description' => 'The Guideline heading number (integer) from the Guidelines List'
            ],
            'heading' => [
                'type' => 'string',
                'description' => 'The Guideline heading name from the Guidelines List',
            ],
            'criteria' => [
                'type' => 'string',
                'description' => 'WCAG criteria',
            ],
            'assessment' => [
                'type' => 'string',
                'enum' => ['Fail', 'Warn'],
                'description' => '"Fail" if the criterion is not met; "Warn" if it meets the criterion but results in poor experience.',
            ],
            'observation' => [
                'type' => 'string',
                'description' => 'Briefly describe how the issue fails to meet the guideline (or why it is only a warning)',
            ],
            'recommendation' => [
                'type' => 'string',
                'description' => 'Brief, actionable steps to fix the accessibility issue.',
            ],
            'testing' => [
                'type' => 'string',
                'description' => 'Brief instructions to verify the issue with assistive technologies or manual checks.',
            ],
            'impact' => [
                'type' => 'string',
                'enum' => ['Critical', 'Serious', 'Moderate', 'Low'],
                'description' => 'Severity of the barrier. "Critical" blocks primary tasks; "Serious" makes them very difficult; "Moderate" makes them somewhat harder; "Low" causes mild inconvenience.',
            ],
        ],
        'required' => [
            'reasoning',
            'number',
            'heading',
            'criteria',
            'assessment',
            'observation',
            'recommendation',
            'testing',
            'impact',
        ],
        'additionalProperties' => false,
    ];

    const array ISSUE_SCHEMA = [
        'type' => 'object',
        'properties' => [
            'target' => [
                'type' => 'string',
                'description' => 'The target of the issue, such as a description of the element or a CSS selector.',
            ],
            ...self::ITEM_SCHEMA['properties'],
        ],
        'required' => [
            'target',
            ...self::ITEM_SCHEMA['required'],
        ],
        'additionalProperties' => false,
    ];

    private AnalyzeIssue $analyzeIssueInstance;
    private StoreGuidelineMatches $storeGuidelineMatchesInstance;
    private FetchGuidelinesDocument $fetchGuidelinesDocumentInstance;
    private FetchGuidelinesList $fetchGuidelinesListInstance;
    private FetchGuidelines $fetchGuidelinesInstance;
    private FetchIssuePageContent $fetchIssuePageContent;

    public function __construct(
        private readonly ChatServiceFactoryInterface $chatServiceFactory,
    ) {
        $this->analyzeIssueInstance = new AnalyzeIssue($this->chatServiceFactory, $this);
        $this->storeGuidelineMatchesInstance = new StoreGuidelineMatches($this);
        $this->fetchGuidelinesDocumentInstance = new FetchGuidelinesDocument();
        $this->fetchGuidelinesListInstance = new FetchGuidelinesList();
        $this->fetchGuidelinesInstance = new FetchGuidelines($this);
        $this->fetchIssuePageContent = new FetchIssuePageContent();
    }

    public static function getAgent(): Agent
    {
        return Agent::findAgent(Agents::GuidelinesAnalyzer);
    }

    public function getTools(): array
    {
        return [
            'analyze_accessibility_issue' => $this->analyzeIssueInstance,
            'store_guideline_matches' => $this->storeGuidelineMatchesInstance,
            'fetch_guidelines_document' => $this->fetchGuidelinesDocumentInstance,
            'fetch_guidelines_list' => $this->fetchGuidelinesListInstance,
            'fetch_guidelines' => $this->fetchGuidelinesInstance,
            'fetch_issue_page_content' => $this->fetchIssuePageContent,
            // 'fetch_issue_context' => $this->fetchIssueContextInstance,
        ];
    }

    public static function analyzeIssue(Issue $issue): array
    {
        return AnalyzeIssueTool::run($issue->id);
    }

    public static function storeItems(Issue $issue, array $items): array
    {
        return StoreGuidelineMatchesTool::run($issue->id, $items);
    }

    public static function populateIssueItemsWithAI(Issue $issue): array
    {
        $items = self::analyzeIssue($issue);

        if (!empty(($items['feedback']))) {
            return $items;
        }

        return self::storeItems($issue, $items);
    }

    public static function getIssueContext(Issue $issue): string
    {
        $issueData = [
            'id' => $issue->id,
            'scope_id' => $issue->scope_id,
            'target' => $issue->target,
            //'css_selector' => $issue->css_selector,
            'description' => $issue->description->toHtml(),
            'page_content' => $issue->scope?->pageHasBeenRetrieved()
                ? 'Available via "fetch_scope_page_content" tool.'
                : 'No page content available.',
        ];

        return "Here is the current issue in JSON format:\n"
            . "```json\n" . json_encode($issueData, JSON_PRETTY_PRINT) . "\n```\n\n";
    }

    public static function getScopeContext(Scope $scope): string
    {
        $scopeData = [
            'id' => $scope->id,
            'title' => $scope->title,
            'url' => $scope->url,
            'page_content' => $scope->pageHasBeenRetrieved()
                ? "Available via 'fetch_scope_page_content($scope->id)' tool."
                : 'No page content available.',
            'notes' => $scope->notes,
            'comments' => $scope->comments,
        ];

        return "Here is the current web page scope in JSON format:\n"
            . "```json\n" . json_encode($scopeData, JSON_PRETTY_PRINT) . "\n```\n\n";
    }

    public static function getScopeIssuesContext(Scope $scope): string
    {

        $issues = $scope->issues()->with('guideline')->get()
            ->map(fn (Issue $issue) => self::mapIssueToSchema($issue))
            ->each(fn ($issue) => $issue['url'] = route('guidelines.show', $issue['number']));

        return empty($issues)
            ? "No issues found for this web page scope."
            : "Here are the issues in JSON format:\n"
            . "```json\n" . $issues->toJson(JSON_PRETTY_PRINT) . "\n```\n\n";
    }

    public static function getIssueSchema(): array
    {
        return self::ISSUE_SCHEMA;
    }

    public static function getItemsSchema(): array
    {
        return self::ITEM_SCHEMA;
    }

    public static function getRecommendedGuidelinesSchema(): array
    {
        return [
            'description' => 'Return a "guidelines" array (if applicable warnings or failures are found) and a "feedback" string',
            'type' => 'object',
            'properties' => [
                'guidelines' => [
                    'type' => ['array', 'null'],
                    'description' => 'Array of applicable guideline objects when accessibility barriers are found. Null if none are applicable.',
                    'items' => self::getItemsSchema(),
                ],
                'feedback' => [
                    'type' => ['string'],
                    'description' => 'A brief explanation or summary, or a clarification request if more information is needed.',
                ],
            ],
            'additionalProperties' => false,
            'required' => ['guidelines', 'feedback'],
        ];
    }

    public static function mapResponseToItemArray(mixed $response): array
    {
        $response = (array) $response; // Ensure we are working with an array

        return [
            'guideline_id' => $response['number'],
            'assessment' => Assessment::fromName($response['assessment']),
            'description' => Str::markdown(htmlentities($response['observation'])),
            'recommendation' => Str::markdown(htmlentities($response['recommendation'])),
            'testing' => Str::markdown(htmlentities($response['testing'])),
            'impact' => Impact::fromName($response['impact']),
            'ai_reasoning' => Str::markdown(htmlentities($response['reasoning'])),
            'ai_status' => AIStatus::Generated,
        ];
    }

    public static function mapIssueToSchema(Issue $issue): array
    {
        return [
            'target' => $issue->target,
            ...self::mapItemToSchema($issue),
        ];
    }

    public static function mapItemToSchema(Issue|Item $item): array
    {
        return [
            'reasoning' => $item->ai_reasoning?->toHtml() ?? '',
            'number' => $item->guideline->number,
            'name' => $item->guideline->name,
            'wcag_criterion' => $item->guideline->criterion->getNumberName(),
            'assessment' => $item->assessment?->value,
            'observation' => $item->description?->toHtml(),
            'recommendation' => $item->recommendation?->toHtml(),
            'testing' => $item->testing?->toHtml(),
            'impact' => $item->impact?->name,
        ];
    }

    public static function mapGuidelineToSchema(Guideline $guideline): array
    {
        return [
            'number'         => $guideline->number,
            'name'           => $guideline->name,
            'wcag_criterion' => $guideline->criterion->getNumberName(),
            'category'       => "{$guideline->category->name}: {$guideline->category->description}",
            'text'           => $guideline->notes,
            'url'            => route('guidelines.show', $guideline),
        ];
    }
}
