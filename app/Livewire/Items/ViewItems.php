<?php

namespace App\Livewire\Items;

use App\Events\ItemChanged;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewItems extends Component
{
    public Issue $issue;
    public ?Item $editItem;
    public ?string $selectedImage = null;
    public ?Guideline $guideline = null;

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

    public function acceptAI(Item $item): void
    {
        $this->authorize('update', $item);
        $item->markAiAccepted();

        $this->dispatch('items-updated');
    }

    public function rejectAI(Item $item): void
    {
        $this->authorize('update', $item);
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

    #[On('show-guideline')]
    public function showGuideline($number): void
    {
        $this->guideline = Guideline::find($number);
        $this->modal('show-guideline')->show();
    }

    #[On('close-guideline')]
    public function closeGuideline(): void
    {
        $this->modal('show-guideline')->close();
        $this->guideline = null;
    }

    public function delete(Item $item): void
    {
        $this->authorize('delete', $this->issue);
        $item->delete();

        event(new ItemChanged($item, 'deleted', []));

        $this->dispatch('items-updated');
    }
}
