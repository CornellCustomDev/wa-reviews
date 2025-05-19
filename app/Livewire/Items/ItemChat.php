<?php

namespace App\Livewire\Items;

use App\AiAgents\ItemChatAgent;
use App\Livewire\Ai\LarAgentChat;
use App\Models\Issue;
use Livewire\Component;

class ItemChat extends Component
{
    use LarAgentChat;

    public Issue $issue;

    protected function getAgent(): ItemChatAgent
    {
        if (is_null($this->selectedChatKey)) {
            $this->newChat();
        }
        if ($this->needsRefresh) {
            $this->issue->refresh();
            $this->needsRefresh = false;
        }

        return new ItemChatAgent($this->issue, $this->selectedChatKey);
    }

    public function render()
    {
        return view('ai-agents.laragent-chat', [
            'description' => $this->getDescription(),
        ]);
    }

    public function getDescription(): string
    {
        return <<<DESCRIPTION
This AI chatbot can answer questions and provide recommendations for this accessibility issue and
related guidelines.
DESCRIPTION;
    }
}
