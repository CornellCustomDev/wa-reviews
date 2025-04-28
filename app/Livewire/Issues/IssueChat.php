<?php

namespace App\Livewire\Issues;

use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use App\Services\GuidelinesAnalyzer\Tools\Tool;
use Livewire\Component;

class IssueChat extends Component
{
    private OpenAIChatService $chat;
    private ChatServiceFactoryInterface $chatServiceFactory;
    private GuidelinesAnalyzerService $guidelinesAnalyzer;

    public Issue $issue;

    public array $messages;
    public string $userMessage = '';

    public function __construct()
    {
        $this->chatServiceFactory = app(ChatServiceFactoryInterface::class);
        $this->guidelinesAnalyzer = app(GuidelinesAnalyzerServiceInterface::class);

        $this->chat = $this->chatServiceFactory->make(ChatProfile::Chat);
    }

    public function mount(Issue $issue): void
    {
        $this->issue = $issue;
        $this->messages = session()->get('issue_chat.' . $this->issue->id, []);
    }

    public function sendUserMessage(): void
    {
        $this->chat->setPrompt($this->getChatPrompt());
        $this->chat->setMessages($this->getMessageHistory());
        $this->chat->addUserMessage($this->userMessage);
        $this->chat->setTools($this->getTools());

        $newMessages = $this->chat->send();

        // See if any newMessages are tool_calls to store_guideline_matches
        foreach ($newMessages as $message) {
            if ($message['tool_call']['function']['name'] ??= 'store_guideline_matches') {
                $this->dispatch('items-updated');
            }
        }

        $this->setMessageHistory($this->chat->getMessages());
        $this->userMessage = '';
    }

    private function getMessageHistory(): array
    {
        $this->messages = session()->get('issue_chat.' . $this->issue->id, []);

        return $this->messages;
    }

    private function setMessageHistory(array $messages): void
    {
        $this->messages = $messages;

        session()->put('issue_chat.' . $this->issue->id, $messages);
    }

    public function clearChat(): void
    {
        $this->chat = $this->chatServiceFactory->make(ChatProfile::Chat);
        $this->setMessageHistory([]);
        $this->userMessage = '';
    }

    private function getTools(): array
    {
        $tools = $this->guidelinesAnalyzer->getTools();

        return [
            'review_guideline_applicability' => $tools['review_guideline_applicability'],
            'store_guideline_matches' => $tools['store_guideline_matches'],
            'fetch_guidelines_document' => $tools['fetch_guidelines_document'],
            'fetch_guidelines_list' => $tools['fetch_guidelines_list'],
            'fetch_guidelines' => $tools['fetch_guidelines'],
            'fetch_issue_page_content' => $tools['fetch_issue_page_content'],
        ];
    }

    public function getToolCallTool(array $toolCall): Tool
    {
        return $this->getTools()[$toolCall['function']['name']];
    }

    public function getChatPrompt(): string
    {
        // Build the issue context block
        $issueContext = $this->getIssueContext();

        // Provide only the fields the model really needs from each guideline item
        $guidelinesContext = $this->issue->items()->with('guideline')->get()
            ->map(fn ($item) => $this->guidelinesAnalyzer->mapItemToSchema($item))
            ->each(fn ($item) => $item['url'] = config('app.url') . '/guidelines/' . $item['number'])
            ->toJson(JSON_PRETTY_PRINT);

        // Get the names of all available tools to help the model knows what it can call
        $toolNames = implode(', ', array_keys($this->getTools()));

        return <<<PROMPT
You are an expert in the Cornell web accessibility testing guidelines for WCAG 2.2 AA
(which the user calls "accessibility issues" or similar).
Your task is to help the user find applicable guidelines for the issue described below.
Always ground your answers in the provided context. The user can see the issue details
and the applicable guidelines that have been identified.

If you need more data, **call one of the available tools by name**. If you need the user
to clarify something, ask them directly.

Available tools: {$toolNames}

If you have recommended guidelines, you can use the "store_guideline_matches" tool to store them
for the user.

### Issue
{$issueContext}

### Applicable Guidelines
```json
{$guidelinesContext}
```

— When you cite a Guideline, reference its **"number"** field. You should also link to the Guideline using it's URL,
  for example: [Guideline 2](https://SITE_URL/guideline/2).
— Keep answers concise unless the user explicitly asks for detail.
- Only provide help related to the tasks and tools you have available.
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
