<?php

namespace App\Livewire;

use App\Services\AzureOpenAI\ChatService;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Chat extends Component
{
    public mixed $prompt;
    public array $messages = [];
    public string $userMessage = '';


    public function mount(): void
    {
        $chatService = app(ChatService::class);
        $this->prompt = $chatService->prompt;
        $this->messages = $chatService->getMessages();
    }

    public function sendMessage(): void
    {
        $chatService = app(ChatService::class);

        // Hydrate the ChatService with the message context
        $chatService->setMessages($this->messages);
        // Add the current message
        $chatService->addMessage($this->userMessage);

        try {
            $chatService->send();
            $this->userMessage = '';
        } catch (Exception $e) {
            // TODO Decide how we want to handle errors
            dd($e);
        }

        // Update the messages with the response
        $this->messages = $chatService->getMessages();
    }

}
