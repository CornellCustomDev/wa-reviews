<?php

namespace App\Livewire\Ai;

use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Services\CornellAI\ChatServiceFactory;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Livewire\Component;

class GuidelineHelp extends Component
{
    private ChatServiceFactory $chatServiceFactory;
    private GuidelinesAnalyzerService $guidelinesAnalyzer;
    public Issue $issue;

    public bool $useGuidelines = true;
    public array $guidelines = [];
    public string $response;
    public string $feedback = '';

    public bool $showChat = false;
    public array $messageHistory;
    public array $chatMessages;
    public string $userMessage = '';
    public string $debug = '';

    public function __construct()
    {
        $this->chatServiceFactory = app(ChatServiceFactoryInterface::class);
        $this->guidelinesAnalyzer = app(GuidelinesAnalyzerServiceInterface::class);
    }

    public function populateGuidelines(): void
    {
        $this->authorize('update', $this->issue);

        $this->showChat = false;
        $this->feedback = '';

        $result = $this->guidelinesAnalyzer->populateIssueItemsWithAI($this->issue);

        $this->response = json_encode($result, JSON_PRETTY_PRINT);

        if (!empty(($result['feedback']))) {
            if (!is_string($result['feedback'])) {
                $result['feedback'] = json_encode($result['feedback'], JSON_PRETTY_PRINT);
            }
            $this->feedback = $result['feedback'];
        }

        $this->dispatch('items-updated');
    }

    public function sendChatMessage(): void
    {
        $chat = $this->chatServiceFactory->make(ChatProfile::Task);

        $chat->setPrompt($this->getChatPrompt());
        $chat->setMessages($this->messageHistory ?: []);
        $chat->addUserMessage($this->userMessage);
        $chat->setTools($this->guidelinesAnalyzer->getTools());
        $chat->send();

        $this->messageHistory = $chat->getMessages();
        $this->chatMessages = $chat->getChatMessages();
        $this->response = $chat->getLastAiResponse();
        $this->userMessage = '';

        $this->debug = json_encode($this->messageHistory, JSON_PRETTY_PRINT);
    }

    public function clearChat(): void
    {
        $this->messageHistory = [];
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
        // Build the issue context block
        $issueContext = $this->getIssueContext();

        // Provide only the fields the model really needs from each guideline item
        $guidelinesContext = $this->issue->items
            ->map(fn ($item) => $this->guidelinesAnalyzer->mapItemToSchema($item))
            ->toJson(JSON_PRETTY_PRINT);

        // Get the names of all available tools to help the model knows what it can call
        $toolNames = implode(', ', array_keys($this->guidelinesAnalyzer->getTools()));

        return <<<PROMPT
You are an expert the Cornell web accessibility testing guidelines for WCAG 2.2 AA.
Your job is to help the user understand and remediate the issue described below.
Always ground your answers in the provided context.
If you need more data, **call one of the available tools by name**.

Available tools: {$toolNames}

### Issue
{$issueContext}

### Applicable Guidelines
```json
{$guidelinesContext}
```

— When you cite a guideline, reference its **"number"** field.
— Keep answers concise unless the user explicitly asks for detail.
PROMPT;
    }

    private function getIssueContext(): string
    {
        $issueData = [
            'id' => $this->issue->id,
            'target' => $this->issue->target,
            'css_selector' => $this->issue->css_selector,
            'description' => $this->issue->description,
        ];

        return "Here is the current issue in JSON format:\n```json\n" . json_encode($issueData, JSON_PRETTY_PRINT) . "\n```\n\n";
    }


}
