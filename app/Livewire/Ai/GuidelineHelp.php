<?php

namespace App\Livewire\Ai;

use App\Models\Issue;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class GuidelineHelp extends Component
{
    public Issue $issue;

    public bool $useGuidelines = true;
    public array $guidelines = [];
    public string $response;
    public string $feedback = '';

    public bool $showChat = false;
    public array $chatMessages;
    public string $userMessage = '';

    public function populateGuidelines(): void
    {
        $this->authorize('update', $this->issue);

        $this->showChat = false;
        $this->feedback = '';

        $result = GuidelinesAnalyzerService::populateIssueItemsWithAI($this->issue);

        $this->response = json_encode($result, JSON_PRETTY_PRINT);

        if (isset($result['feedback'])) {
            $this->response = json_encode($result, JSON_PRETTY_PRINT);
            $this->feedback = $result['feedback'];
            return;
        }

        $this->dispatch('items-updated');
    }

    public function sendChatMessage()
    {
        $chat = app(OpenAIChatService::class);
        $chat->setPrompt($this->getChatPrompt());
        if (!empty($this->chatMessages)) {
            $chat->setMessages($this->chatMessages);
        }
        $chat->addMessage($this->userMessage);
        $chat->send();
        $this->chatMessages = $chat->getMessages();
        $this->response = $chat->getLastAiResponse();
        $this->userMessage = '';
    }

    public function clearChat()
    {
        $this->chatMessages = [];
        $this->response = '';
        $this->userMessage = '';
    }

    public function render()
    {
        return view('livewire.ai.guideline-help');
    }

    public function getChatPrompt(): string
    {
        $prompt = GuidelinesAnalyzerService::getGuidelinesDocumentPrompt();
//
//        // If there are no items, provide the guidelines document so people can ask about Guidelines
//        if ($this->issue->items->isEmpty()) {
//            $prompt .=
//        }

        $prompt .= "# Task\n\nYou are an expert in web accessibility guidelines, your task is to assist users in understanding applicable guidelines for specific web accessibility issues.\n\n";

        // Include the page content early, for caching
        if ($this->issue->scope->page_content) {
            $prompt .= "# Context: Web page being analyzed\n\n```html\n{$this->issue->scope->page_content}\n```\n\n";
        }

        $prompt .= "# Context: Web accessibility issue\n\n";
        $prompt .= $this->getIssueContext();
        if ($this->issue->items->isNotEmpty()) {
            $prompt .= "The following applicable guidelines have been identified:\n";
            $prompt .= "```json\n".json_encode($this->issue->items()->with('guideline')->get(), JSON_PRETTY_PRINT)."\n```\n\n";
        }

        return $prompt . <<<PROMPT
# Desired Outcome:

The final output should be informative, succinct, and user-friendly, allowing users to easily understand the relevance
and application of web accessibility guidelines in relation to their specific issues. Aim for clarity and brevity in
your descriptions to facilitate quick comprehension.
PROMPT;
    }

    private function getIssueContext(): string
    {
        $issueContext = sprintf(
            "In the target location of \"%s\" the following issue was found: \n%s\n\n",
            $this->issue->target,
            $this->issue->description
        );
        return $issueContext;
    }
}
