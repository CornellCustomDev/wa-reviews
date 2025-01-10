<?php

namespace App\Livewire\Ai;

use App\Enums\Assessment;
use App\Models\Issue;
use App\Models\Item;
use App\Services\AccessibilityAnalyzer\AccessibilityAnalyzerService;
use App\Services\AzureOpenAI\ChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
        $this->showChat = false;
        $this->feedback = '';

        $result = GuidelinesAnalyzerService::populateIssueItemsWithAI($this->issue);

        if (isset($result['feedback'])) {
            $this->response = json_encode($result, JSON_PRETTY_PRINT);
            $this->feedback = $result['feedback'];
            return;
        }

        $this->dispatch('items-updated');
    }

    public function sendChatMessage()
    {
        $chat = ChatService::make();
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
        $issueContext = $this->getIssueContext();
        $itemsContext = $this->issue->items->isNotEmpty()
            ? json_encode($this->issue->items, JSON_PRETTY_PRINT)
            : json_encode('No applicable guidelines have been identified.');
        $pageContent = $this->issue->scope->page_content ?? '';

        $prompt = <<<PROMPT
As an expert in web accessibility guidelines, your task is to assist users in understanding applicable guidelines for specific web accessibility issues.

## Context:
- The user has provided an accessibility issue: $issueContext
- If there are any applicable guidelines that have been identified, they are listed here:
```JSON
$itemsContext
```

## Desired Outcome:
The final output should be informative, succinct, and user-friendly, allowing users to easily understand the relevance and application of web accessibility guidelines in relation to their specific issues. Aim for clarity and brevity in your descriptions to facilitate quick comprehension.

## Page content:
```html
{$pageContent}
```

## The content of the Guidelines Document follows.

PROMPT;

        return $prompt . Storage::get('guidelines.md');
    }

    private function getIssueContext(): string
    {
        $issueContext = sprintf(
            "In the target location of \"%s\" the following issue was found: \n%s",
            $this->issue->target,
            $this->issue->description
        );
        return $issueContext;
    }
}
