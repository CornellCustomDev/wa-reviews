<?php

namespace App\AiAgents;

use App\AiAgents\Tools\AnalyzeIssueTool;
use App\AiAgents\Tools\StoreGuidelineMatchesTool;
use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Messages\SystemMessage;

class IssueAnalyzerAgent extends Agent
{
    protected Issue $issue;

    protected $history = 'file';

    protected $tools = [
        AnalyzeIssueTool::class,
        StoreGuidelineMatchesTool::class,
    ];

    public function __construct(Issue $issue, string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Task->value]['model'];

        $this->issue = $issue;

        parent::__construct($key);
    }

    protected function buildSessionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            class_basename($this),
            $this->issue->id,
            $this->getChatKey()
        );
    }

    public function instructions(): string
    {
        $guidelineUrl = route('guidelines.show', 2);

        return <<<PROMPT
You are a tool-using agent tasked with analyzing web accessibility issues and storing the results. You have
two tools to accomplish this: "analyze_accessibility_issue" and "store_guideline_matches".

# Instructions
1. Use the "analyze_accessibility_issue" tool to analyze the issue context provided by the user.
2. If there are any applicable guidelines, use the "store_guideline_matches" tool to store the results of the analysis.
3. If there are no applicable guidelines, look at the feedback returned.
4. Report back a brief summary of what you were able to accomplish with the tools in a concise manner.

— If you cite a Guideline, reference its **"number"** field. You should also link to the Guideline using it's URL,
  for example: [Guideline 2]($guidelineUrl).
- The user is not able to respond to you, so you should not ask them any questions.

PROMPT;
    }

    public function getContext(): string
    {
        return "# Context: Web accessibility issue\n\n" .
            GuidelinesAnalyzerService::getIssueContext($this->issue);
    }

    protected function beforeSaveHistory(ChatHistoryInterface $history): true
    {
        // Remove the instructions from the history
        if ($history[0] instanceof SystemMessage) {
            $history->truncateOldMessages(1);
        }

        return true;
    }
}
