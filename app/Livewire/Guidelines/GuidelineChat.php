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
        $agent = auth()->check()
            ? GuidelineChatAgent::forUser(auth()->user())
            : GuidelineChatAgent::for(crc32(session()->getId()));
        $agent->setGuideline($this->guideline);

        return $agent;
    }

}
