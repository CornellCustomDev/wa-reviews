<?php

namespace App\Livewire\Ai;

use App\Services\CornellAI\OpenAIChatService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Prompt extends Component
{
    public mixed $prompt;
    public bool $includeGuidelines = true;
    public array $messages = [];
    public string $userMessage = '';


    public function mount(): void
    {
        $chatService = app(OpenAIChatService::class);
        $this->prompt = ChatOld::getPrompt();
        $this->messages = $chatService->getMessages();
    }

    public function sendMessage(): void
    {
        $chatService = app(OpenAIChatService::class);

        $prompt = $this->prompt . ($this->includeGuidelines ? Storage::get('guidelines.md') : '');
        $chatService->setPrompt($prompt);
        $chatService->setMessages($this->messages);
        $chatService->addUserMessage($this->userMessage);

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

    public function render()
    {
        return view('livewire.ai.prompt');
    }
}
