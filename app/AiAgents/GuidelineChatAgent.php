<?php

namespace App\AiAgents;

use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchGuidelinesDocumentTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\Models\Guideline;
use Throwable;

class GuidelineChatAgent extends ModelChatAgent
{
    protected $tools = [
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchGuidelinesDocumentTool::class,
    ];

    public function __construct(Guideline $context, string $key)
    {
        parent::__construct($context, $key);
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.GuidelineChat.instructions', [
            'tools' => $this->getTools(),
            'guideline' => $this->context,
        ])->render();
    }
}
