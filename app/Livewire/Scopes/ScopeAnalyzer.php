<?php

namespace App\Livewire\Scopes;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\PrismAction;
use App\Ai\Prism\PrismSchema;
use App\Ai\Prism\Tools\StoreIssuesTool;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Livewire\Component;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Text\Response;
use UnexpectedValueException;

class ScopeAnalyzer extends Component
{
    use PrismAction;
    use PrismSchema;

    public Scope $scope;

    public function recommendGuidelines(): void
    {
        // Authorize because we add issues
        $this->authorize('update', $this->scope);

        $context = "# Context: Web page scope needing review for accessibility issues\n"
            . GuidelinesAnalyzerService::getScopeContext($this->scope);

        $this->userMessage = $context;
        $this->initiateAction();
    }

    public function getAgent(): GuidelineRecommenderAgent
    {
        return GuidelineRecommenderAgent::for($this->scope)
            ->withPrompt($this->userMessage);
    }

    protected function getContextModel(): Scope
    {
        return $this->scope;
    }

    protected function afterAgentResponse(Response $prismResponse): void
    {
        $this->sendStreamMessage('Retrieving response... ({:elapsed}s)');

        try {
            $schema = $this->convertToPrismSchema(GuidelinesAnalyzerService::getRecommendedGuidelinesSchema());
            $response = $this->getStructuredResponse($schema, $prismResponse->text);
        } catch (UnexpectedValueException $e) {
            $this->feedback = "Error processing response: " . $e->getMessage();
            $this->showFeedback = true;
            return;
        }

        if ($response?->feedback) {
            $this->feedback = $response->feedback;
            $this->showFeedback = true;
        }

        if ($response?->guidelines) {
            $storeIssuesTool = new StoreIssuesTool();
            $storeIssuesTool($this->scope->id, $response->guidelines);
            $this->dispatch('issues-updated');
        }
    }
}
