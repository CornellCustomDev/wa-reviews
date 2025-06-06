<?php

namespace App\Livewire\Issues;

use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Models\Item;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Models\ChatHistory;

class IssueChatOld extends Component
{
    private OpenAIChatService $chat;
    private ChatServiceFactoryInterface $chatServiceFactory;
    private GuidelinesAnalyzerServiceInterface $guidelinesAnalyzer;

    public Issue $issue;

    public array $messages = [];
    public string $userMessage = '';
    public ?Collection $chats = null;
    public ?ChatHistory $selectedChat = null;
    public ?int $selectedChatId = null;

    public function __construct()
    {
        $this->chatServiceFactory = app(ChatServiceFactoryInterface::class);
        $this->guidelinesAnalyzer = app(GuidelinesAnalyzerServiceInterface::class);

        $this->chat = $this->chatServiceFactory->make(ChatProfile::Chat);
    }

    public function mount(Issue $issue): void
    {
        $this->issue = $issue;
        $this->loadChats();
    }

    private function loadChats(): void
    {
        $this->chats = $this->issue->chats(auth()->user())
            ->select(['id', 'name', 'updated_at'])
            ->orderBy('updated_at')
            ->get();
    }

    public function sendUserMessage(): void
    {
        $this->chat->setPrompt($this->getChatPrompt());
        $this->chat->setMessages($this->messages);
        $this->chat->addUserMessage($this->userMessage);
        $this->chat->setTools($this->getTools());

        $newMessages = $this->chat->send();

        // See if any newMessages are tool_calls to store_guideline_matches
        foreach ($newMessages as $message) {
            if (($message['tool_call']['function']['name'] ?? null) === 'store_guideline_matches') {
                $this->dispatch('items-updated');
            }
        }

        $this->setMessageHistory($this->chat->getMessages());
        $this->userMessage = '';
    }

    private function setMessageHistory(array $messages): void
    {
        $this->messages = $messages;

        if ($this->selectedChat) {
            $this->selectedChat->messages = $messages;
            $this->selectedChat->name = $this->generateHistoryName($messages);
            $this->selectedChat->save();
        } else {
            $this->selectedChat = $this->issue->chats(auth()->user())->create([
                'user_id' => auth()->id(),
                'context_type' => Issue::class,
                'context_id' => $this->issue->id,
                'messages' => $messages,
                'name' => $this->generateHistoryName($messages),
            ]);
        }

        $this->loadChats();
    }

    public function selectChat($chatId): void
    {
        $this->selectedChat = ChatHistory::findOrFail($chatId);
        $this->messages = $this->selectedChat->messages;
    }

    public function clearChat(): void
    {
        $this->chat = $this->chatServiceFactory->make(ChatProfile::Chat);
        $this->messages = [];
        $this->selectedChat = null;
        $this->userMessage = '';
    }

    public function deleteChat(): void
    {
        if ($this->selectedChat) {
            $this->selectedChat->delete();
            $this->loadChats();
            $this->clearChat();
        }
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
            'analyze_accessibility_issue' => $tools['analyze_accessibility_issue'],
        ];
    }

    public function getToolCallInformation(array $toolCall): string
    {
        $tool = $this->getTools()[$toolCall['function']['name']];
        $name = $tool->getName();
        $args = $toolCall['function']['arguments'];

        return "$name ($args)";
    }

    private function getChatPrompt(): string
    {
        // Build the issue context block
        $issueContext = $this->getIssueContext();

        // Provide only the fields the model really needs from each guideline item
        $guidelinesContext = $this->issue->items()->with('guideline')->get()
            ->map(fn (Item $item) => $this->guidelinesAnalyzer->mapItemToSchema($item))
            ->each(fn ($item) => $item['url'] = route('guidelines.show', $item['number']))
            ->toJson(JSON_PRETTY_PRINT);

        // Get the names of all available tools to help the model knows what it can call
        $toolNames = '';
        foreach($this->getTools() as $tool) {
            $toolNames .= ' - ' . $tool->getName() . ': ' . $tool->getDescription() . "\n";
        }

        $guidelineUrl = route('guidelines.show', 2);

        return <<<PROMPT
You are an expert in the Cornell web accessibility testing guidelines for WCAG 2.2 AA (which the user calls
"accessibility issues" or similar). Your task is to help the user find applicable guidelines for the issue described
below. Always ground your answers in the provided context. The user can see the issue details
and the applicable guidelines that have been identified. Keep answers concise unless the user explicitly asks for detail.

You should be cautious in making assessments about applicable guidelines, consulting the available tools when
appropriate. If you need more data, **call one of the available tools by name**. If you need the user
to clarify something, ask them directly, such as "Can you please clarify what you mean by X in the issue description?".

If the user asks about something unrelated to the task, politely inform them that you can only help with
the task at hand.

Available tools:
{$toolNames}

Always confirm with the user before using store_guideline_matches.

### Issue
{$issueContext}

### Applicable Guidelines
These have already been stored an associated with the issue.
```json
{$guidelinesContext}
```

— When you cite a Guideline, reference its **"number"** field and link to it like [Guideline {number}]({url}),
  for example: [Guideline 2]($guidelineUrl).
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

    private function generateHistoryName(array $messages): string
    {
        // Use AI to come up with a name for the chat history
        $taskChat = $this->chatServiceFactory->make(ChatProfile::Task);
        $chatMessages = json_encode(array_filter($messages, fn($m) => $m['role'] !== 'tool'), JSON_PRETTY_PRINT);
        $currentName = $this->selectedChat?->name ?? 'not set';
        $chatHistoryNames = $this->chats->pluck('name')->join(', ');

        $prompt = <<<PROMPT
You are naming an AI chat history to be used in a menu of chat histories for accessibility issues and guidelines. Use
names that will help the user understand the unique context of this chat history.

## Instructions

1. Generate a very short name for the AI chat messages (see below).
2. The current name is $currentName
3. If the generated name is quite similar to the current name, keep the current name.
4. Do not use an existing name of another chat history: $chatHistoryNames.

Return only the name, without any other text.

## Example Response
- Guideline 3 Reviewed
- Review Page Content
- Reviewed 2 Guidelines

## Chat Messages
```json
$chatMessages
```
PROMPT;

        $taskChat->setPrompt($prompt);
        $taskChat->send();

        return $taskChat->getLastAiResponse();
    }
}
