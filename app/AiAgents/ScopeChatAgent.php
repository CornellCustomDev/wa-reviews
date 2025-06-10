<?php

namespace App\AiAgents;

use App\AiAgents\Tools\AnalyzeScopeTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchScopePageContentTool;
use App\AiAgents\Tools\ScratchPadTool;
use App\AiAgents\Tools\StoreIssuesTool;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Throwable;

class ScopeChatAgent extends ModelChatAgent
{
    protected $tools = [
        ScratchPadTool::class,
        AnalyzeScopeTool::class,
        StoreIssuesTool::class,
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchScopePageContentTool::class,
    ];

    public function __construct(Scope $context, string $key)
    {
        parent::__construct($context, $key);
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.ScopeChat.instructions', [
            'tools' => $this->getTools(),
            'scopeContext' => GuidelinesAnalyzerService::getScopeContext($this->context),
            // Provide only the fields the model really needs from each guideline item
            'issuesContext' => GuidelinesAnalyzerService::getScopeIssuesContext($this->context),
        ])->render();
    }
}
