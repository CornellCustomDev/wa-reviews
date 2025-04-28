<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Enums\Agents;
use App\Models\Agent;
use App\Models\Guideline;
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
        $this->fetchGuidelinesInstance = new FetchGuidelines($this);
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

        return $this->storeItems($issue, $items);
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
                    'description' => '"Fail" if the criterion is not met; "Warn" if it meets the criterion but results in poor experience.',
                ],
                'observation' => ['type' => 'string'],
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
    }

    public function mapItemToSchema(Item $item): array
    {
        return [
            'reasoning' => $item->ai_reasoning ?? '',
            'number' => $item->guideline->number,
            'name' => $item->guideline->name,
            'wcag_criterion' => $item->guideline->criterion->getNumberName(),
            'assessment' => $item->assessment,
            'observation' => $item->description,
            'recommendation' => $item->recommendation,
            'testing' => $item->testing,
            'impact' => $item->impact,
        ];
    }

    public function mapGuidelineToSchema(Guideline $guideline): array
    {
        return [
            'number'         => $guideline->number,
            'name'           => $guideline->name,
            'wcag_criterion' => $guideline->criterion->getNumberName(),
            'category'       => "{$guideline->category->name}: {$guideline->category->description}",
            'text'           => $guideline->notes,
            'url'            => config('app.url') . '/guidelines/' . $guideline->id,
        ];
    }
}
