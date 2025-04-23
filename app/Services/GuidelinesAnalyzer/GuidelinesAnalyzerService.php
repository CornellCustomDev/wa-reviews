<?php

namespace App\Services\GuidelinesAnalyzer;

use App\Enums\Agents;
use App\Models\Agent;
use App\Models\Issue;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\Tools\AnalyzeIssue;
use App\Services\GuidelinesAnalyzer\Tools\StoreGuidelineMatches;
use Illuminate\Support\Facades\Storage;

class GuidelinesAnalyzerService implements GuidelinesAnalyzerServiceInterface
{
    private AnalyzeIssue $analyzeIssueInstance;
    private StoreGuidelineMatches $storeGuidelineMatchesInstance;

    public function __construct(
        private readonly ChatServiceFactoryInterface $chatServiceFactory,
    ) {
        $this->analyzeIssueInstance = new AnalyzeIssue($this->chatServiceFactory, $this);
        $this->storeGuidelineMatchesInstance = new StoreGuidelineMatches($this);
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
            // 'fetch_guidelines_document' => $this->fetchGuidelinesDocumentInstance,
            // 'fetch_issue_context' => $this->fetchIssueContextInstance,
        ];
    }

    public function analyzeIssue(Issue $issue): array
    {
        return $this->analyzeIssueInstance->analyze($issue);
    }

    public function storeItems(Issue $issue, array $items): array
    {
        return $this->storeGuidelineMatchesInstance->store($issue, $items);
    }

    public function populateIssueItemsWithAI(Issue $issue): array
    {
        $result = $this->analyzeIssue($issue);

        // TODO: Handle this feedback in a more meaningful way
        if (!empty(($result['feedback']))) {
            return $result;
        }

        return $this->storeItems($issue, $result);
    }

    public static function getGuidelinesDocumentPrompt(): string
    {
        return <<<PROMPT
# Guidelines Document

When instructions refer to the Guidelines Document, it is the document below. When Guideline numbers are mentioned,
they are the numbered sections in the Guidelines Document.

## Guidelines Document content

PROMPT
            . Storage::get('guidelines-list.md') . "\n\n";
    }

}
