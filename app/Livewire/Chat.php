<?php

namespace App\Livewire;

use App\Services\AzureOpenAI\ChatService;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Chat extends Component
{
    public array $messages = [];
    public string $userMessage = '';


    public function mount(): void
    {
        $chatService = app(ChatService::class);
        $this->messages = $chatService->getMessages();
    }

    // Respond to submitting the form
    public function sendMessage(): void
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $this->userMessage,
        ];
        $chatService = app(ChatService::class);
        $chatService->setMessages($this->messages);
        try {
            $chatService->send();
            $this->userMessage = '';
        } catch (Exception $e) {
            // TODO Decide how we want to handle errors
            dd($e);
        }
        $this->messages = $chatService->getMessages();

        $markdown = "**This is markdown bold text**";
        $html = \Str::of($markdown)->markdown();

    }

}
