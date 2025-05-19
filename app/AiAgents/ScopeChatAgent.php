<?php

namespace App\AiAgents;

use App\AiAgents\Tools\FetchGuidelinesDocumentTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchScopePageContentTool;
use App\AiAgents\Tools\ScratchPadTool;
use App\Models\Issue;
use App\Models\Scope;
use Throwable;

class ScopeChatAgent extends ModelChatAgent
{
    protected $tools = [
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchGuidelinesDocumentTool::class,
        FetchScopePageContentTool::class,
        ScratchPadTool::class,
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
            'scope' => $this->context,
            'issueContexts' => $this->context->issues->map(function (Issue $issue) {
                return [
                    'id' => $issue->id,
                    'target' => $issue->target,
                    'css_selector' => $issue->css_selector,
                    'description' => $issue->description,
                ];
            }),
        ])->render();
    }
}
