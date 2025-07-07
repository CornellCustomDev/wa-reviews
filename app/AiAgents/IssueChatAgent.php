<?php

namespace App\AiAgents;

use App\AiAgents\Tools\AnalyzeIssueTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchScopePageContentTool;
use App\AiAgents\Tools\ScratchPadTool;
use App\AiAgents\Tools\UpdateIssueTool;
use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Throwable;

class IssueChatAgent extends ModelChatAgent
{
    protected $tools = [
        AnalyzeIssueTool::class,
        FetchGuidelinesListTool::class,
        FetchGuidelinesTool::class,
        FetchScopePageContentTool::class,
        UpdateIssueTool::class,
        ScratchPadTool::class,
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

        $guideline = $issue->guideline;

        if (!$guideline) {
            return json_encode('No guideline associated with this issue.');
        }

        $guidelineArray = [
            'reasoning' => $issue->ai_reasoning?->toHtml() ?? '',
            'number' => $guideline->number,
            'name' => $guideline->name,
            'wcag_criterion' => $guideline->criterion->getNumberName(),
            'assessment' => $issue->assessment?->value,
            'observation' => $issue->description?->toHtml(),
            'recommendation' => $issue->recommendation?->toHtml(),
            'testing' => $issue->testing?->toHtml(),
            'impact' => $issue->impact?->name,
            'url' => route('guidelines.show', $guideline->number),
        ];

        $guidelinesContext = json_encode($guidelineArray, JSON_PRETTY_PRINT);

        return view('ai-agents.IssueChat.instructions', [
            'issueContext' => GuidelinesAnalyzerService::getIssueContext($issue),
            'guidelinesContext' => $guidelinesContext,
            'guidelineUrl' => route('guidelines.show', 5),
        ])->render();
    }
}
