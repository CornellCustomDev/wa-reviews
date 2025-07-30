<?php

namespace App\Livewire\Issues;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\Handlers\GuidelineRecommenderCallback;
use App\Ai\Prism\PrismAction;
use App\Events\IssueChanged;
use App\Events\ItemChanged;
use App\Models\ChatHistory;
use App\Models\Issue;
use App\Models\Item;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class IssueAnalyzer extends Component
{
    use PrismAction;

    public Issue $issue;
    public ?Collection $recommendations = null;

    public function unreviewedItems(): Collection
    {
        return $this->issue->items
            ->filter(fn(Item $item) => $item->hasUnreviewedAI())
            ?: collect();
    }

    #[Computed(persist: true)]
    public function hasUnreviewedItems(): bool
    {
        return $this->unreviewedItems()->isNotEmpty();
    }

    public function recommendGuidelines(): void
    {
        if (empty($this->issue->scope)) {
            $this->feedback = 'A Scope is required to recommend guidelines.';
            $this->showFeedback = true;
            return;
        }

        // If target or description are empty, give feedback to the user
        if (empty($this->issue->target) || empty($this->issue->description)) {
            $this->feedback = 'Target and description are required to recommend guidelines.';
            $this->showFeedback = true;
            return;
        }

        // Authorize because we delete existing items
        $this->authorize('update', $this->issue->scope);

        // Remove any existing recommendation for this issue first
        $this->issue->items()->delete();
        unset($this->hasUnreviewedItems);

        $this->initiateAction();
    }

    protected function getAgent(): GuidelineRecommenderAgent
    {
        $userMessage = "# Context: Web accessibility issue\n"
            . GuidelinesAnalyzerService::getIssueContext($this->issue);

        return GuidelineRecommenderAgent::for($this->issue->scope)
            ->withPrompt($userMessage)
            ->withContextModel($this->issue)
            ->withResponseHandler(new GuidelineRecommenderCallback(
                fn ($guidelines, $chatHistory) => $this->storeGeneratedItems($guidelines, $chatHistory)
            ));
    }

    private function storeGeneratedItems(array $guidelines, ?ChatHistory $chatHistory): void
    {
        // Create an item for each guideline
        foreach ($guidelines as $guideline) {
            $item = Item::create([
                'issue_id' => $this->issue->id,
                ...GuidelinesAnalyzerService::mapResponseToItemArray($guideline),
                'chat_history_ulid' => $chatHistory?->ulid,
            ]);

            event(new ItemChanged($item, 'created', $item->getAttributes(), $chatHistory?->agent));
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
        $this->authorize('update', $this->issue);

        $item = $this->issue->items->firstWhere('guideline_id', $guidelineNumber);
        $this->issue->applyRecommendation($item);
        event(new IssueChanged($this->issue, 'updated'));

        $this->showFeedback = false;
        unset($this->hasUnreviewedItems);

        $this->dispatch('items-updated');
        $this->dispatch('close-confirm-recommendation');
    }

    public function rejectAI($guidelineNumber): void
    {
        $this->authorize('update', $this->issue);

        $item = $this->issue->items->firstWhere('guideline_id', $guidelineNumber);
        $item->markAiRejected();
        $item->delete();

        unset($this->hasUnreviewedItems);
        $this->dispatch('items-updated');
    }
}
