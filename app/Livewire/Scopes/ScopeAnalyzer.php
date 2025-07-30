<?php

namespace App\Livewire\Scopes;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\Handlers\GuidelineRecommenderCallback;
use App\Ai\Prism\PrismAction;
use App\Events\IssueChanged;
use App\Models\ChatHistory;
use App\Models\Issue;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Livewire\Component;

class ScopeAnalyzer extends Component
{
    use PrismAction;

    public Scope $scope;

    public function recommendGuidelines(): void
    {
        // Authorize because we add issues
        $this->authorize('update', $this->scope);

        $this->initiateAction();
    }

    public function getAgent(): GuidelineRecommenderAgent
    {
        $userMessage = "# Context: Web page scope needing review for accessibility issues\n"
            . GuidelinesAnalyzerService::getScopeContext($this->scope);

        return GuidelineRecommenderAgent::for($this->scope)
            ->withPrompt($userMessage)
            ->withResponseHandler(new GuidelineRecommenderCallback(
                fn ($guidelines, $chatHistory) => $this->storeGeneratedIssues($guidelines, $chatHistory)
            ));
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

        $this->dispatch('issues-updated');
    }
}
