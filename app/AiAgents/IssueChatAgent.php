<?php

namespace App\AiAgents;

use App\AiAgents\Tools\AnalyzeIssueTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchGuidelinesDocumentTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchIssuePageContentTool;
use App\AiAgents\Tools\ReviewGuidelineApplicabilityTool;
use App\AiAgents\Tools\StoreGuidelineMatchesTool;
use App\Models\Issue;
use App\Models\Item;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Throwable;

class IssueChatAgent extends ModelChatAgent
{
    protected $tools = [
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchGuidelinesDocumentTool::class,
        FetchIssuePageContentTool::class,
        AnalyzeIssueTool::class,
        ReviewGuidelineApplicabilityTool::class,
        StoreGuidelineMatchesTool::class,
    ];

    public function __construct(Issue $context, string $key) {
        return parent::__construct($context, $key);
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        $issue = $this->context;

        // Provide only the fields the model really needs from each guideline item
        $guidelinesContext = $issue->items()->with('guideline')->get()
            ->map(fn (Item $item) => GuidelinesAnalyzerService::mapItemToSchema($item))
            ->each(fn ($item) => $item['url'] = route('guidelines.show', $item['number']))
            ->toJson(JSON_PRETTY_PRINT);

        return view('ai-agents.IssueChat.instructions', [
            'tools' => $this->getTools(),
            'issueContext' => GuidelinesAnalyzerService::getIssueContext($issue),
            'guidelinesContext' => $guidelinesContext,
            'guidelineUrl' => route('guidelines.show', 5),
        ])->render();
    }
}
