<?php

namespace App\Livewire\Scopes;

use App\AiAgents\ScopeChatAgent;
use App\Livewire\Ai\LarAgentChat;
use App\Models\Scope;
use Livewire\Component;

class ScopeChat extends Component
{
    use LarAgentChat;

    public Scope $scope;

    protected function getAgent(): ScopeChatAgent
    {
        if (is_null($this->selectedChatKey)) {
            $this->newChat();
        }
        if ($this->needsRefresh) {
            $this->scope->refresh();
            $this->needsRefresh = false;
        }

        return new ScopeChatAgent($this->scope, $this->selectedChatKey);
    }
}
