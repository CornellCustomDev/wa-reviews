<?php

namespace App\Livewire\Guidelines;

use App\AiAgents\GuidelineChatAgent;
use App\Models\Guideline;
use Illuminate\Support\Collection;
use LarAgent\Agent;
use LarAgent\Core\Contracts\Message;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GuidelineChat extends Component
{
    public Guideline $guideline;

    public array $messages = [];
    public string $userMessage = '';

    private function getAgent(): GuidelineChatAgent
    {
        $agent = auth()->check()
            ? GuidelineChatAgent::forUser(auth()->user())
            : GuidelineChatAgent::for(crc32(session()->getId()));
        $agent->setGuideline($this->guideline);

        return $agent;
    }

    #[Computed]
    public function chats(): Collection
    {
        return collect();
    }

    #[Computed]
    public function chatMessages(): Collection
    {
        return $this->getChatMessagesForUser($this->getAgent());
    }

    public function clearChat(): void
    {
        $this->getAgent()->clear();
    }

    public function sendUserMessage(): void
    {
        $this->getAgent()->respond($this->userMessage);

        unset($this->chatMessages);
        $this->userMessage = '';
    }

    private function getChatMessagesForUser(Agent $agent)
    {
        return collect($agent->chatHistory()->getMessages())
            ->filter(fn(Message $m) => $m->getRole() !== 'system')
            ->map(fn($m) => $m->toArray());
    }
}
