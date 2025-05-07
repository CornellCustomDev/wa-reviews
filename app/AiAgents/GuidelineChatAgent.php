<?php

namespace App\AiAgents;

use App\AiAgents\Tools\FetchGuidelines;
use App\AiAgents\Tools\FetchGuidelinesDocument;
use App\AiAgents\Tools\FetchGuidelinesList;
use App\Enums\ChatProfile;
use App\Models\Guideline;
use Illuminate\Support\Collection;
use LarAgent\Agent;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\History\FileChatHistory;
use Throwable;

class GuidelineChatAgent extends Agent
{
    protected $tools = [
        FetchGuidelines::class,
        FetchGuidelinesList::class,
        FetchGuidelinesDocument::class,
    ];

    private Guideline $context;

    public function __construct(Guideline $context, string $key)
    {
        $this->provider = config('cornell_ai.laragent_profile');
        $this->model = config('cornell_ai.profiles')[ChatProfile::Chat->value]['model'];

        $this->context = $context;

        parent::__construct($key);
    }

    /**
     * @throws Throwable
     */
    public function instructions(): string
    {
        return view('ai-agents.GuidelineChat.instructions', [
            'tools' => $this->getTools(),
            'guideline' => $this->context,
        ])->render();
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
            return new ModelChatHistory($this->getChatKey(), $this->context, auth()->user());
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
}
