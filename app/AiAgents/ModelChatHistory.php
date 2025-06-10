<?php

namespace App\AiAgents;

use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use LarAgent\Core\Abstractions\ChatHistory;
use LarAgent\Core\Contracts\ChatHistory as ChatHistoryInterface;
use LarAgent\Messages\SystemMessage;

class ModelChatHistory extends ChatHistory implements ChatHistoryInterface
{
    protected Guideline|Issue|Scope $context;

    protected bool $saveChatKeys = false;
    protected User $user;
    private ModelChatAgent $agent;

    public function __construct(ModelChatAgent $agent, string $name, Guideline|Issue|Scope $context, User $user)
    {
        $this->agent = $agent;
        $this->context = $context;
        $this->user = $user;

        parent::__construct(
            name: $name,
            options: [
                'context_window' => 100000,
                'store_meta' => false,
                'save_chat_keys' => false,
            ],
        );
    }

    public function readFromMemory(): void
    {
        $content = $this->chats()->find($this->getIdentifier());

        // We remove the instructions from the chat history, so put them back in
        $messages = $content ? $this->buildMessages([
            (new SystemMessage($this->agent->instructions()))->toArray(),
            ...$content->messages
        ]) : [];

        $this->setMessages($messages);
    }

    public function writeToMemory(): void
    {
        // Remove the instructions from the history
        $firstMessage = $this->messages[0] ?? null;
        if ($firstMessage && ($firstMessage instanceof SystemMessage || $firstMessage['role'] === 'system')) {
            $this->truncateOldMessages(1);
        }

        $messages = $this->toArrayForStorage();

        /** @var \App\Models\ChatHistory $content */
        $this->chats()->updateOrCreate([
            'ulid' => $this->getIdentifier(),
            'user_id' => auth()->id(),
            'context_type' => get_class($this->context),
            'context_id' => $this->context->id,
        ], [
            'messages' => $messages,
        ]);
    }

    public function saveKeyToMemory(): void
    {
        // Not implemented
    }

    public function loadKeysFromMemory(): array
    {
        return $this->loadChatsFromMemory()->keys()->toArray();
    }

    public function loadChatsFromMemory(): Collection
    {
        return $this->chats()
            ->where('context_type', get_class($this->context))
            ->where('context_id', $this->context->id)
            ->select(['ulid', 'name', 'updated_at'])
            ->orderByDesc('updated_at')
            ->get()
            ->keyBy('ulid');
    }

    public function removeChatFromMemory(string $key): void
    {
        $this->chats()->where('ulid', $key)->delete();
    }

    protected function removeChatKey(string $key): void
    {
        // Not implemented
    }

    public function setName(string $name): void
    {
        $this->chats()->where('ulid', $this->getIdentifier())->update(['name' => $name]);
    }

    private function chats(): MorphMany
    {
        return $this->context->chats($this->user);
    }
}
