<?php

namespace App\Livewire\Scopes;

use App\AiAgents\ModelChatAgent;
use App\AiAgents\ScopeChatAgent;
use App\Livewire\Ai\LarAgentChat;
use App\Models\Scope;
use Livewire\Component;

class ScopeChat extends Component
{
    use LarAgentChat {
        afterAgentResponse as baseAfterAgentResponse;
    }

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

    protected function afterAgentResponse(ModelChatAgent $agent): void
    {
        $this->baseAfterAgentResponse($agent);

        if (in_array('store_issues', $agent->getToolsCalled())) {
            $this->dispatch('issues-updated');
        }
    }
}
