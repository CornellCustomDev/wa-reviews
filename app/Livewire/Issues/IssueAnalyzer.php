<?php

namespace App\Livewire\Issues;

use App\Ai\Prism\Agents\GuidelineRecommenderAgent;
use App\Ai\Prism\PrismAction;
use App\Ai\Prism\PrismSchema;
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
use Prism\Prism\Text\Response;
use UnexpectedValueException;

class IssueAnalyzer extends Component
{
    use PrismAction;
    use PrismSchema;

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

        $context = "# Context: Web accessibility issue\n"
            . GuidelinesAnalyzerService::getIssueContext($this->issue);

        $this->userMessage = $context;
        $this->initiateAction();
    }

    protected function getAgent(): GuidelineRecommenderAgent
    {
        return GuidelineRecommenderAgent::for($this->issue->scope)
            ->withPrompt($this->userMessage);
    }

    protected function getContextModel(): Issue
    {
        return $this->issue;
    }

    protected function afterAgentResponse(Response $prismResponse): void
    {
        $this->sendStreamMessage('Retrieving response... ({:elapsed}s)');

        try {
            $schema = $this->convertToPrismSchema(GuidelinesAnalyzerService::getRecommendedGuidelinesSchema());
            $response = $this->getStructuredResponse($prismResponse, $schema, $this->issue);
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
            $this->storeGeneratedItems($response->guidelines, $this->getChatHistory());
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

    private function storeGeneratedItems(array $guidelines, ?ChatHistory $chatHistory): void
    {
        foreach ($guidelines as $guideline) {
            // Create an item for each guideline
            $item = Item::create([
                'issue_id' => $this->issue->id,
                ...GuidelinesAnalyzerService::mapResponseToItemArray($guideline),
                'chat_history_ulid' => $chatHistory?->ulid,
            ]);

            event(new ItemChanged($item, 'created', $item->getAttributes(), $chatHistory?->agent));
        }
    }
}
