<?php

namespace App\Livewire\Issues;

use App\AiAgents\CreateIssueChatAgent;
use App\AiAgents\ModelChatAgent;
use App\Livewire\Ai\LarAgentChat;
use App\Models\Scope;
use Livewire\Component;

class CreateIssueChat extends Component
{
    use LarAgentChat {
        afterAgentResponse as baseAfterAgentResponse;
    }

    public Scope $scope;

    protected function getAgent(): CreateIssueChatAgent
    {
        if (is_null($this->selectedChatKey)) {
            $this->newChat();
        }
        if ($this->needsRefresh) {
            $this->issue->refresh();
            $this->needsRefresh = false;
        }

        return new CreateIssueChatAgent($this->scope, $this->selectedChatKey);
    }

    protected function afterAgentResponse(ModelChatAgent $agent): void
    {
        $this->baseAfterAgentResponse($agent);

        // @TODO implement this tool and an event listener for update-fields
        if (in_array('update_fields', $agent->getToolsCalled())) {
            $this->dispatch('update-fields');
        }
    }
}
