<?php

namespace App\Livewire\Issues;

use App\Enums\ChatProfile;
use App\Models\Issue;
use App\Services\CornellAI\ChatServiceFactoryInterface;
use App\Services\CornellAI\OpenAIChatService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use App\Services\GuidelinesAnalyzer\Tools\Tool;
use Illuminate\Support\Collection;
use Livewire\Component;
use App\Models\ChatHistory;

class IssueChat extends Component
{
    private OpenAIChatService $chat;
    private ChatServiceFactoryInterface $chatServiceFactory;
    private GuidelinesAnalyzerService $guidelinesAnalyzer;

    public Issue $issue;

    public array $messages;
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
            if ($message['tool_call']['function']['name'] ??= 'store_guideline_matches') {
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
                'user_id' => auth()->user()->id,
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
            ->map(fn ($item) => $this->guidelinesAnalyzer->mapItemToSchema($item))
            ->each(fn ($item) => $item['url'] = config('app.url') . '/guidelines/' . $item['number'])
            ->toJson(JSON_PRETTY_PRINT);

        // Get the names of all available tools to help the model knows what it can call
        $toolNames = implode(', ', array_keys($this->getTools()));

        $guidelineUrl = route('guidelines.show', 2);

        return <<<PROMPT
You are an expert in the Cornell web accessibility testing guidelines for WCAG 2.2 AA
(which the user calls "accessibility issues" or similar).
Your task is to help the user find applicable guidelines for the issue described below.
Always ground your answers in the provided context. The user can see the issue details
and the applicable guidelines that have been identified.

If you need more data, **call one of the available tools by name**. If you need the user
to clarify something, ask them directly.

If the user asks about something unrelated to the task, politely inform them that you can only help with
the task at hand.

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
  for example: [Guideline 2]($guidelineUrl).
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

    private function generateHistoryName(array $messages): string
    {
        // Use AI to come up with a name for the chat history
        $taskChat = $this->chatServiceFactory->make(ChatProfile::Task);
        $chatMessages = json_encode(array_filter($messages, fn($m) => $m['role'] !== 'tool'), JSON_PRETTY_PRINT);
        $currentName = $this->selectedChat?->name ?? 'not set';

        $prompt = <<<PROMPT
You are naming an AI chat history to be used in a menu of chat histories for accessibility issues and guidelines. Use
names that will help the user understand the unique context of this chat history.

## Instructions

1. Generate a very short name for the AI chat messages (see below).
2. The current name is $currentName
3. If the generated name is quite similar to the current name, keep the current name.

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

        "You are naming an AI chat history to be used in a menu of chat histories for accessibility issues and guidelines. "
        . "The menu is in a UI context of accessibility issues and guidelines, so stick to names that will help "
        . "the user understand the unique context of this chat history.\n\n"
        . "1. Generate a very short name for the following AI chat messages:\n\n"
        . "```json\n" . json_encode($chatMessages, JSON_PRETTY_PRINT) . "\n```\n\n"
        . "2. The current name is: {$this->selectedChat?->name}\n"
        . "3. If the generated name is quite similar to the current name, keep the current name.\n\n"
        . "Return only the name, without any other text.\n\n"
        . "### Example\n";
        $taskChat->setPrompt($prompt);
        $taskChat->send();

        return $taskChat->getLastAiResponse();
    }
}
