<?php

namespace App\AiAgents;

use App\AiAgents\Tools\AnalyzeIssueTool;
use App\AiAgents\Tools\FetchGuidelinesListTool;
use App\AiAgents\Tools\FetchGuidelinesTool;
use App\AiAgents\Tools\FetchIssuePageContentTool;
use Illuminate\Support\Collection;

class ItemChatAgent extends IssueChatAgent
{
    protected $history = 'in_memory';

    protected $tools = [
        FetchGuidelinesTool::class,
        FetchGuidelinesListTool::class,
        FetchIssuePageContentTool::class,
        //AnalyzeIssueTool::class,
    ];

    public function getChats(): Collection
    {
        return collect();
    }
}
