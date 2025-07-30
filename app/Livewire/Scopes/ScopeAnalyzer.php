<?php

namespace App\Livewire\Scopes;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\PrismAction;
use App\Ai\Prism\PrismSchema;
use App\Events\IssueChanged;
use App\Models\ChatHistory;
use App\Models\Issue;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Livewire\Component;
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
            $response = $this->getStructuredResponse($prismResponse, $schema, $this->scope);
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
            $this->storeGeneratedIssues($response->guidelines, $this->chatHistory);
            $this->dispatch('issues-updated');
        }
    }

    private function storeGeneratedIssues($guidelines, ?ChatHistory $chatHistory): void
    {
        $existingIssues = $this->scope->issues()->pluck('guideline_id')->toArray();

        // Create an issue for each guideline
        foreach ($guidelines as $guideline) {
            // Sometimes the guideline is a stdClass object, so convert it to an array
            $guideline = (array)$guideline;

            // Filter any issues that are already in the issues list
            if (in_array($guideline['number'], $existingIssues)) {
                continue;
            }

            $issue = Issue::create([
                'project_id' => $this->scope->project_id,
                'scope_id' => $this->scope->id,
                'target' => $guideline['target'],
                ...GuidelinesAnalyzerService::mapResponseToItemArray($guideline),
                'chat_history_id' => $chatHistory?->ulid,
            ]);

            event(new IssueChanged($issue, 'created', $issue->getAttributes()), $chatHistory?->agent);
        }
    }
}
