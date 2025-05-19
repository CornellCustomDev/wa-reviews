<?php
 namespace App\AiAgents;

use App\Enums\ChatProfile;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Scope;
use Illuminate\Support\Collection;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Core\Contracts\Tool as ToolInterface;
use LarAgent\History\FileChatHistory;

abstract class ModelChatAgent extends Agent
{
    protected Guideline|Issue|Scope $context;
    protected array $toolsCalled = [];

    public function __construct(Guideline|Issue|Scope $context, string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];

        $this->context = $context;

        parent::__construct($key);
    }

    protected function buildSessionId(): string
    {
        return sprintf(
            '%s_%s_%s',
            class_basename($this->context),
            $this->context->id,
            $this->getChatKey()
        );
    }

    public function createChatHistory(string $sessionId): ChatHistoryInterface
    {
        if (auth()->check()) {
            return new ModelChatHistory($this, $this->getChatKey(), $this->context, auth()->user());
        } else {
            // TODO: Store anonymous chats in the database
            return new FileChatHistory($sessionId);
        }
    }

    public function updateChatName(): void
    {
        $chatHistory = $this->chatHistory();
        if ($chatHistory instanceof ModelChatHistory) {
            $this->setChatName($chatHistory);
        }
    }

    public function getChatKeys(): array
    {
        return $this->chatHistory()->loadKeysFromMemory();
    }

    public function getChats(): Collection
    {
        $chatHistory = $this->chatHistory();

        if ($chatHistory instanceof ModelChatHistory) {
            return $chatHistory->loadChatsFromMemory();
        } else {
            // TODO: Remove when we store anonymous chats in the database
            return collect();
        }
    }

    public function getChatMessages(string $chatKey): array
    {
        $this->setChatSessionId($chatKey);
        $this->setupChatHistory();

        return $this->chatHistory()->getMessages();
    }

    public function deleteChat(string $chatKey): void
    {
        $this->setChatSessionId($chatKey);
        $this->setupChatHistory();

        $this->chatHistory()->removeChatFromMemory($chatKey);
    }

    public function setChatName(ModelChatHistory $chatHistory): void
    {
        $chats = $chatHistory->loadChatsFromMemory();
        $currentName = $chats->get($chatHistory->getIdentifier())->name ?? 'New chat';
        $chatHistoryNames = $chats->pluck('name')->toArray();

        $name = ChatHistoryNameAgent::for($chatHistory->getIdentifier())
            ->getChatName($chatHistory, $currentName, $chatHistoryNames);

        $newName = trim($name);
        if ($newName !== $currentName) {
            $chatHistory->setName($newName);
        }
    }

    protected function afterToolExecution(ToolInterface $tool, mixed &$result): bool
    {
        $this->toolsCalled[] = $tool->getName();

        return true;
    }

    public function getToolsCalled(): array
    {
        return $this->toolsCalled;
    }
}
