<?php

namespace App\Livewire\Ai;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LarAgent\Core\Contracts\Message;
use LarAgent\Core\Contracts\ToolCall;
use LarAgent\Core\Enums\Role;
use LarAgent\Messages\StreamedAssistantMessage;
use LarAgent\Messages\ToolCallMessage;
use Livewire\Attributes\Computed;

trait LarAgentChat
{
    public ?string $selectedChatKey = null;
    public Collection $messages;
    public string $userMessage = '';
    public bool $streaming = false;
    // Populated via wire:stream
    public string $streamedResponse = '';
    public bool $needsRefresh = false;
    public string $feedback = '';
    public bool $showFeedback = false;

    #[Computed(persist: true)]
    public function chats(): Collection
    {
        return $this->getAgent()->getChats();
    }

    #[Computed(persist: true)]
    public function chatMessages(): Collection
    {
        $messages = $this->getAgent()->getChatMessages($this->selectedChatKey);
        $this->messages = collect($messages)
            ->filter(fn(Message $m) => $m->getRole() !== Role::SYSTEM->value)
            ->map(fn($m) => $m->toArray());

        return $this->messages;
    }

    public function newChat(): void
    {
        $this->selectedChatKey = Str::ulid();
        $this->messages = collect();
        unset($this->chatMessages);
        $this->userMessage = '';
        $this->streaming = false;
        $this->feedback = '';
        $this->showFeedback = false;
    }

    public function selectChat($chatKey): void
    {
        $this->selectedChatKey = $chatKey;
        $this->messages = collect();
        unset($this->chatMessages);
        $this->dispatch('scroll-to-bottom');
    }

    public function deleteChat(): void
    {
        if (is_null($this->selectedChatKey)) {
            return;
        }

        $this->getAgent()->deleteChat($this->selectedChatKey);
        unset($this->chats);
        $this->newChat();
    }

    public function sendUserMessage(): void
    {
        $this->streaming = true;
        $this->dispatch('scroll-to-bottom');
        $this->js('$wire.streamUserMessage()');
    }

    public function streamUserMessage(): void
    {
        $this->stream('streamedResponse', 'Retrieving response...');
        $start = microtime(true);

        $agent = $this->getAgent();
        try {
            $stream = $agent->respondStreamed($this->userMessage);
            foreach ($stream as $chunk) {
                $elapsed = round(microtime(true) - $start, 1);
                if ($chunk instanceof ToolCallMessage) {
                    /** @var ToolCall $toolCall */
                    foreach ($chunk->getToolCalls() as $toolCall) {
                        $this->stream('streamedResponse', "Calling '{$toolCall->getToolName()}'... ({$elapsed}s)", true);
                    }
                }
                if ($chunk instanceof StreamedAssistantMessage) {
                    $this->stream('streamedResponse', "Retrieving response... ({$elapsed}s)", true);
                }
            }
            $elapsed = round(microtime(true) - $start, 1);
            $this->stream('streamedResponse', "Response received in {$elapsed} seconds.", true);
            $this->userMessage = '';

            $agent->updateChatName();
            unset($this->chats);
            unset($this->chatMessages);
        } catch (\Exception $e) {
            $this->feedback = $e->getMessage();
            $this->showFeedback = true;
        }

        $this->streaming = false;
        $this->needsRefresh = true;
        $this->dispatch('scroll-to-bottom');
    }
}
