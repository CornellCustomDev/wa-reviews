<?php

namespace App\Livewire\Items;

use App\Enums\AIStatus;
use App\Events\ItemChanged;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewItems extends Component
{
    public Issue $issue;
    public ?Item $editItem;
    public ?string $selectedImage = null;

    #[On('items-updated')]
    public function refreshIssue(): void
    {
        $this->issue->refresh();
    }

    public function edit(Item $item): void
    {
        $this->editItem = $item;
        $this->modal('edit-item')->show();
    }

    #[On('close-edit')]
    public function closeEdit(): void
    {
        $this->modal('edit-item')->close();
        $this->editItem = null;
    }

    #[Computed]
    public function hasUnreviewedAI(): bool
    {
        return $this->issue->items
            ->filter(fn(Item $item) => $item->hasUnreviewedAI())
            ->isNotEmpty();
    }

    public function acceptAI(Item $item): void
    {
        $this->authorize('update', $item->issue);
        $item->markAiAccepted();

        $this->dispatch('items-updated');
    }

    public function rejectAI(Item $item): void
    {
        $this->authorize('update', $item->issue);
        $item->markAiRejected();

        $item->delete();

        $this->dispatch('items-updated');
    }

    public function viewImage(string $imageUrl): void
    {
        $this->selectedImage = $imageUrl;
        $this->modal('view-image')->show();
    }

    public function closeImage(): void
    {
        $this->modal('view-image')->close();
        $this->selectedImage = null;
    }

    public function delete(Item $item): void
    {
        $this->authorize('delete', $this->issue);
        $item->delete();

        event(new ItemChanged($item, 'deleted', []));

        $this->dispatch('items-updated');
    }
}
