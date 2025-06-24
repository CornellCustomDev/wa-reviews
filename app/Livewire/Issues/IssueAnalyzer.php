<?php

namespace App\Livewire\Issues;

use App\AiAgents\GuidelineRecommenderAgent;
use App\AiAgents\Tools\StoreGuidelineMatchesTool;
use App\Livewire\Ai\LarAgentAction;
use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LarAgent\Agent;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class IssueAnalyzer extends Component
{
    use LarAgentAction;

    public Scope $scope;
    public ?IssueForm $form = null;
    public ?Issue $issue = null;
    public ?Collection $recommendations = null;

    protected function getAgent(): GuidelineRecommenderAgent
    {
        if ($this->needsRefresh) {
            $this->issue?->refresh();
            $this->needsRefresh = false;
        }

        return new GuidelineRecommenderAgent($this->scope, Str::ulid());
    }

    public function unreviewedItems(): Collection
    {
        return $this->issue?->items
            ->filter(fn(Item $item) => $item->hasUnreviewedAI())
            ?: collect();
    }

    #[Computed(persist: true)]
    public function hasUnreviewedItems(): bool
    {
        return $this->unreviewedItems()->isNotEmpty();
    }

    public function getContext($form = null): string
    {
        return "# Context: Web accessibility issue\n"
            . GuidelinesAnalyzerService::getIssueContext($this->issue);
    }

    public function recommendGuidelines($form = null): void
    {
        // Authorize because we delete existing items
        $this->authorize('update', $this->scope);

        // Remove any existing recommendation for this issue first
        $this->issue?->items()->delete();
        unset($this->hasUnreviewedItems);

        $this->userMessage = $this->getContext($form);
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

        if ($response?->guidelines) {
            StoreGuidelineMatchesTool::call($this->issue->id, $response->guidelines);
            unset($this->hasUnreviewedItems);
        }
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
        $this->issue->applyRecommendation($item->id);

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
