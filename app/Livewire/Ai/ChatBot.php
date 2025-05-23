<?php

namespace App\Livewire\Ai;

use App\Services\CornellAI\OpenAIChatService;
use Livewire\Component;

abstract class ChatBot extends Component
{
    public bool $showChat = false;
    public array $chatMessages;
    public string $userMessage = '';
    public string $response;
    public string $feedback = '';

    public function sendChatMessage(): void
    {
        $chat = app(OpenAIChatService::class);
        $chat->setPrompt($this->getChatPrompt());
        if (!empty($this->chatMessages)) {
            $chat->setMessages($this->chatMessages);
        }
        $chat->addUserMessage($this->userMessage);
        $chat->send();
        $this->chatMessages = $chat->getMessages();
        $this->response = $chat->getLastAiResponse();
        $this->userMessage = '';
    }

    public function clearChat(): void
    {
        $this->chatMessages = [];
        $this->response = '';
        $this->userMessage = '';
    }

    abstract public function getChatPrompt(): string;
}
