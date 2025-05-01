<?php

namespace App\Livewire\Guidelines;

use App\AiAgents\GuidelineChatAgent;
use App\Models\Guideline;
use Illuminate\Support\Collection;
use LarAgent\Agent;
use LarAgent\Core\Contracts\Message;
use LarAgent\Messages\StreamedAssistantMessage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class GuidelineChat extends Component
{
    public Guideline $guideline;

    public array $messages = [];
    public string $userMessage = '';
    public bool $streaming = false;
    public string $streamedResponse = '';
    public bool $scrollToBottom = false;

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
        $this->streaming = true;
        $this->js('$wire.streamUserMessage()');
    }

    public function streamUserMessage(): void
    {
        $this->stream('streamedResponse', 'Retrieving response...');
        $start = microtime(true);

        $stream = $this->getAgent()->respondStreamed($this->userMessage);
        $this->userMessage = '';
        foreach ($stream as $chunk) {
            if ($chunk instanceof StreamedAssistantMessage) {
                $elapsed = round(microtime(true) - $start, 1);
                $this->stream('streamedResponse', "Retrieving response... ({$elapsed}s)", true);
            }
        }
        $elapsed = round(microtime(true) - $start, 1);
        $this->stream('streamedResponse', "Response received in {$elapsed} seconds.", true);
        $this->scrollToBottom = true;
    }

    private function getChatMessagesForUser(Agent $agent)
    {
        return collect($agent->chatHistory()->getMessages())
            ->filter(fn(Message $m) => $m->getRole() !== 'system')
            ->map(fn($m) => $m->toArray());
    }
}
