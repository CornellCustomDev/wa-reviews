<?php

namespace App\Livewire\Scopes;

use App\AiAgents\ScopeAnalyzerAgent;
use App\AiAgents\Tools\StoreIssuesTool;
use App\Livewire\Ai\LarAgentAction;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Str;
use LarAgent\Agent;
use Livewire\Component;

class ScopeAnalyzer extends Component
{
    use LarAgentAction;

    public Scope $scope;

    protected function getAgent(): ScopeAnalyzerAgent
    {
        if ($this->needsRefresh) {
            $this->scope->refresh();
            $this->needsRefresh = false;
        }

        return new ScopeAnalyzerAgent($this->scope, Str::ulid());
    }

    public function getContext(?string $additionalContext = null): string
    {
        return "# Context: Web page scope\n"
            . GuidelinesAnalyzerService::getScopeContext($this->scope);
    }

    public function recommendGuidelines(): void
    {
        $this->userMessage = $this->getContext();
        $this->initiateAction();
    }

    protected function afterAgentResponse(Agent $agent): void
    {
        $content = $agent->lastMessage()->getContent();
        $response = json_decode($content);

        if ($response?->feedback) {
            $this->feedback = $response->feedback;
            $this->showFeedback = true;
        }

        if ($response?->issues) {
            StoreIssuesTool::call($this->scope->id, $response->issues);
            $this->dispatch('issues-updated');
        }
    }
}
