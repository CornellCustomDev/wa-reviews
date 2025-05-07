<?php

namespace App\Livewire\Guidelines;

use App\AiAgents\GuidelineChatAgent;
use App\Livewire\Ai\LarAgentChat;
use App\Models\Guideline;
use Livewire\Component;

class GuidelineChat extends Component
{
    use LarAgentChat;

    public Guideline $guideline;

    protected function getAgent(): GuidelineChatAgent
    {
        if (is_null($this->selectedChatKey)) {
            $this->newChat();
        }
        if ($this->needsRefresh) {
            $this->guideline->refresh();
            $this->needsRefresh = false;
        }

        return new GuidelineChatAgent($this->guideline, $this->selectedChatKey);
    }
}
