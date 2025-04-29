<?php

namespace App\Livewire\Issues;

use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Livewire\Component;

class IssueAnalyzer extends Component
{
    public Issue $issue;
    public bool $showFeedback = false;
    public string $feedback;

    private OpenAIChatService $chat;
    private ChatServiceFactoryInterface $chatServiceFactory;
    private GuidelinesAnalyzerService $guidelinesAnalyzer;

    public function __construct()
    {
        $this->chatServiceFactory = app(ChatServiceFactoryInterface::class);
        $this->guidelinesAnalyzer = app(GuidelinesAnalyzerServiceInterface::class);

        $this->chat = $this->chatServiceFactory->make(ChatProfile::Task);
    }

    public function populateGuidelines(): void
    {
        $this->authorize('update', $this->issue);

        $this->feedback = '';

        $this->chat->setPrompt($this->getPopulateGuidelinesPrompt());
        $this->chat->setTools($this->guidelinesAnalyzer->getTools());

        $this->chat->send();

        $this->feedback = $this->chat->getLastAiResponse();
        $this->showFeedback = true;

        $this->dispatch('items-updated');

    }

    private function getPopulateGuidelinesPrompt(): string
    {
        $guidelineUrl = route('guidelines.show', 2);

        return <<<PROMPT
You are a tool-using agent tasked with analyzing web accessibility issues and storing the results.
You have two tools to accomplish this: "analyze_accessibility_issue" and "store_guideline_matches".

# Instructions
1. Use the "analyze_accessibility_issue" tool to analyze the issue context provided by the user.
2. If there are any applicable guidelines, use the "store_guideline_matches" tool to store the results of the analysis.
3. If there are no applicable guidelines, look at the feedback returned.
4. Report back a brief summary of what you were able to accomplish with the tools in a concise manner.

— If you cite a Guideline, reference its **"number"** field. You should also link to the Guideline using it's URL,
  for example: [Guideline 2]($guidelineUrl).
- The user is not able to respond to you, so you should not ask them any questions.

# Issue Context
{$this->guidelinesAnalyzer->getIssueContext($this->issue)}

PROMPT;
    }
}
