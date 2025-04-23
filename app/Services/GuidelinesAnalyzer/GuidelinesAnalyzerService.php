<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Enums\Agents;
use App\Models\Agent;
use App\Models\Issue;
use App\Models\Item;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\Tools\AnalyzeIssue;
use App\Services\GuidelinesAnalyzer\Tools\FetchGuidelines;
use App\Services\GuidelinesAnalyzer\Tools\FetchGuidelinesDocument;
use App\Services\GuidelinesAnalyzer\Tools\FetchGuidelinesList;
use App\Services\GuidelinesAnalyzer\Tools\FetchIssuePageContent;
use App\Services\GuidelinesAnalyzer\Tools\ReviewGuidelineApplicability;
use App\Services\GuidelinesAnalyzer\Tools\StoreGuidelineMatches;

class GuidelinesAnalyzerService implements GuidelinesAnalyzerServiceInterface
{
    private AnalyzeIssue $analyzeIssueInstance;
    private StoreGuidelineMatches $storeGuidelineMatchesInstance;
    private ReviewGuidelineApplicability $reviewApplicabilityInstance;
    private FetchGuidelinesDocument $fetchGuidelinesDocumentInstance;
    private FetchGuidelinesList $fetchGuidelinesListInstance;
    private FetchGuidelines $fetchGuidelinesInstance;
    private FetchIssuePageContent $fetchIssuePageContent;

    public function __construct(
        private readonly ChatServiceFactoryInterface $chatServiceFactory,
    ) {
        $this->analyzeIssueInstance = new AnalyzeIssue($this->chatServiceFactory, $this);
        $this->storeGuidelineMatchesInstance = new StoreGuidelineMatches($this);
        $this->reviewApplicabilityInstance = new ReviewGuidelineApplicability($this->chatServiceFactory, $this);
        $this->fetchGuidelinesDocumentInstance = new FetchGuidelinesDocument();
        $this->fetchGuidelinesListInstance = new FetchGuidelinesList();
        $this->fetchGuidelinesInstance = new FetchGuidelines();
        $this->fetchIssuePageContent = new FetchIssuePageContent();
    }

    public static function getAgent(): Agent
    {
        return Agent::firstWhere('name', Agents::GuidelinesAnalyzer->value);
    }


    public function getTools(): array
    {
        return [
            'analyze_accessibility_issue' => $this->analyzeIssueInstance,
            'store_guideline_matches' => $this->storeGuidelineMatchesInstance,
            'review_guideline_applicability' => $this->reviewApplicabilityInstance,
            'fetch_guidelines_document' => $this->fetchGuidelinesDocumentInstance,
            'fetch_guidelines_list' => $this->fetchGuidelinesListInstance,
            'fetch_guidelines' => $this->fetchGuidelinesInstance,
            'fetch_issue_page_content' => $this->fetchIssuePageContent,
            // 'fetch_issue_context' => $this->fetchIssueContextInstance,
        ];
    }

    public function analyzeIssue(Issue $issue): array
    {
        return $this->analyzeIssueInstance->analyze($issue);
    }

    public function reviewApplicability(Item $item): array
    {
        return $this->reviewApplicabilityInstance->review($item->issue, $item->guideline_id, $item->toArray());
    }

    private function reviewItems(Issue $issue, array $items): array
    {
        // For each item in the result, get the guideline and the item context and ask AI to confirm it
        $reviewedItems = [];
        foreach ($items as $item) {
            $review = $this->reviewApplicabilityInstance->review($issue, $item['number'], $item);
            $reviewedItems = ['item' => $item, ...$review];
        }

        return $reviewedItems;
    }

    public function storeItems(Issue $issue, array $items): array
    {
        return $this->storeGuidelineMatchesInstance->store($issue, $items);
    }

    public function populateIssueItemsWithAI(Issue $issue): array
    {
        $items = $this->analyzeIssue($issue);

        if (!empty(($items['feedback']))) {
            return $items;
        }

        $result = $this->storeItems($issue, $items);
        return $result;

        // TODO: Apply guardrails before storing the items
        $reviews = $this->reviewItems($issue, $items);
        return ['feedback' => $reviews];
    }

    public function getIssueContext(Issue $issue): string
    {
        $issueData = [
            'id' => $issue->id,
            'target' => $issue->target,
            'css_selector' => $issue->css_selector,
            'description' => $issue->description,
        ];

        return "Here is the current issue in JSON format:\n```json\n" . json_encode($issueData, JSON_PRETTY_PRINT) . "\n```\n\m";
    }

    public function getItemsSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'reasoning' => ['type' => 'string'],
                'number' => ['type' => 'integer'],
                'heading' => ['type' => 'string'],
                'criteria' => ['type' => 'string'],
                'assessment' => [
                    'type' => 'string',
                    'enum' => ['Fail', 'Warn'],
                    'description' => 'Must be one of "Fail" or "Warn".',
                ],
                'applicability' => ['type' => 'string'],
                'recommendation' => ['type' => 'string'],
                'testing' => ['type' => 'string'],
                'impact' => [
                    'type' => 'string',
                    'enum' => ['Critical', 'Serious', 'Moderate', 'Low'],
                    'description' => 'Select one of the four severity levels.',
                ],
            ],
            'required' => [
                'reasoning',
                'number',
                'heading',
                'criteria',
                'assessment',
                'applicability',
                'recommendation',
                'testing',
                'impact',
            ],
            'additionalProperties' => false,
        ];
    }

    public function mapItemToSchema(Item $item): array
    {
        return [
            'reasoning' => $item->ai_reasoning ?? '',
            'number' => $item->guideline_id,
            'heading' => $item->heading,
            'criteria' => $item->criteria,
            'assessment' => $item->assessment,
            'applicability' => $item->description,
            'recommendation' => $item->recommendation,
            'testing' => $item->testing,
            'impact' => $item->impact,
        ];
    }
}
