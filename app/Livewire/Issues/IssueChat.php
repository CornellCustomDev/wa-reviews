<?php

namespace App\Livewire\Issues;

use App\AiAgents\IssueChatAgent;
use App\AiAgents\ModelChatAgent;
use App\Livewire\Ai\LarAgentChat;
use App\Models\Issue;
use Livewire\Component;

class IssueChat extends Component
{
    use LarAgentChat {
        afterAgentResponse as baseAfterAgentResponse;
    }

    public Issue $issue;

    protected function getAgent(): IssueChatAgent
    {
        if (is_null($this->selectedChatKey)) {
            $this->newChat();
        }
        if ($this->needsRefresh) {
            $this->issue->refresh();
            $this->needsRefresh = false;
        }

        return new IssueChatAgent($this->issue, $this->selectedChatKey);
    }

    protected function afterAgentResponse(ModelChatAgent $agent): void
    {
        $this->baseAfterAgentResponse($agent);

        if (in_array('update_issue', $agent->getToolsCalled())) {
            $this->dispatch('items-updated');
        }
    }
}
