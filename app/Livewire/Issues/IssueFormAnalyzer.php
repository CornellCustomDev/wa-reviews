<?php

namespace App\Livewire\Issues;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\Handlers\GuidelineRecommenderCallback;
use App\Ai\Prism\PrismAction;
use App\Livewire\Forms\IssueForm;
use App\Models\ChatHistory;
use App\Models\Item;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class IssueFormAnalyzer extends Component
{
    use PrismAction;

    public ?Scope $scope = null;
    public IssueForm $form;
    public string $userMessage = '';
    public ?Collection $recommendations = null;

    public function unreviewedItems(): Collection
    {
        $items = collect();
        foreach ($this->recommendations as $itemVals) {
            $item = Item::make($itemVals);
            $items->push($item);
        }

        return $items;
    }

    #[Computed(persist: true)]
    public function hasUnreviewedItems(): bool
    {
        return $this->recommendations?->isNotEmpty() ?? false;
    }

    #[On('recommend-guidelines')]
    public function recommendGuidelines($target, $description, $scope_id = null): void
    {
        if ($scope_id) {
            $this->scope = Scope::find($scope_id);
        }

        if (empty($this->scope)) {
            $this->feedback = 'A Scope is required to recommend guidelines.';
            $this->showFeedback = true;
            return;
        }

        // If target or description are empty, give feedback to the user
        if (empty($target) || empty($description)) {
            $this->feedback = 'Target and description are required to recommend guidelines.';
            $this->showFeedback = true;
            return;
        }

        // Remove any existing recommendation for this issue first
        $this->recommendations = collect();
        unset($this->hasUnreviewedItems);

        $this->userMessage = "# Context: Web accessibility issue\n"
            . "- Target element: $target\n"
            . "- Issue description: $description\n"
            . '- Page content: ' . ($this->scope->pageHasBeenRetrieved()
                ? "Available via 'fetch_scope_page_content({$this->scope->id})' tool."
                : 'Not available.')
            . "\n\n";

        $this->initiateAction();
    }

    public function getAgent(): GuidelineRecommenderAgent
    {
        return GuidelineRecommenderAgent::for($this->scope)
            ->withPrompt($this->userMessage)
            ->withResponseHandler(new GuidelineRecommenderCallback(
                fn ($guidelines, $chatHistory) => $this->collectRecommendations($guidelines, $chatHistory)
            ));
    }

    private function collectRecommendations($guidelines, ?ChatHistory $chatHistory): void
    {
        $this->recommendations = collect();
        foreach ($guidelines as $guideline) {
            $itemVals = GuidelinesAnalyzerService::mapResponseToItemArray($guideline);
            $itemVals['chat_history_ulid'] = $chatHistory?->ulid->toString();
            $this->recommendations->push($itemVals);
        }
        unset($this->hasUnreviewedItems);
    }

    public function confirmAI($guidelineNumber): void
    {
        $this->dispatch('show-confirm-recommendation',
            guidelineNumber: $guidelineNumber,
        );
    }

    #[On('ai-accepted')]
    public function acceptAI($guidelineNumber): void
    {
        $item = $this->recommendations->firstWhere('guideline_id', $guidelineNumber);
        $this->dispatch('populate-form', item: $item);

        $this->recommendations = collect();
        $this->showFeedback = false;
        unset($this->hasUnreviewedItems);

        $this->dispatch('close-confirm-recommendation');
    }

    public function rejectAI($guidelineNumber): void
    {
        // Remove the item from the recommendations that has the same guideline_id
        $this->recommendations = $this->recommendations
            ->reject(fn ($rec) => $rec['guideline_id'] == $guidelineNumber);
        if ($this->recommendations->isEmpty()) {
            $this->showFeedback = false;
        }
        unset($this->hasUnreviewedItems);
    }
}
