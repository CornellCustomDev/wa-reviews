<?php

namespace App\Livewire\Ai;

use App\AiAgents\ChatAgent;
use Livewire\Component;

class Chat extends Component
{
    use LarAgentChat;

    protected function getAgent(): ChatAgent
    {
        if (is_null($this->selectedChatKey)) {
            $this->newChat();
        }

        return new ChatAgent($this->selectedChatKey);
    }
}
