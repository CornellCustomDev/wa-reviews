<?php

namespace App\Livewire\Scopes;

use App\Ai\Prism\Agents\ScopeAnalyzerAgent;
use App\Ai\Prism\PrismAction;
use App\Ai\Prism\Tools\StoreIssuesTool;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Livewire\Component;
use Prism\Prism\Text\Response;

class ScopeAnalyzer extends Component
{
    use PrismAction;

    public Scope $scope;

    public function recommendGuidelines(): void
    {
        // Authorize because we add issues
        $this->authorize('update', $this->scope);

        $context = "# Context: Web page scope\n"
            . GuidelinesAnalyzerService::getScopeContext($this->scope);

        $this->userMessage = $context;
        $this->initiateAction();
    }

    public function getAgent(): ScopeAnalyzerAgent
    {
        return ScopeAnalyzerAgent::for($this->scope)
            ->withPrompt($this->userMessage);
    }

    protected function afterAgentResponse(Response $prismResponse): void
    {
        $response = json_decode($prismResponse->text);

        if ($response?->feedback) {
            $this->feedback = $response->feedback;
            $this->showFeedback = true;
        }

        if ($response?->issues) {
            $storeIssuesTool = new StoreIssuesTool();
            $storeIssuesTool($this->scope->id, $response->issues);
            $this->dispatch('issues-updated');
        }
    }
}
