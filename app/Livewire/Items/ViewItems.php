<?php

namespace App\Livewire\Items;

use App\Events\ItemChanged;
use App\Models\Issue;
use App\Models\Item;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
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

    public function closeEdit(): void
    {
        $this->modal('edit-item')->close();
        $this->editItem = null;
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

        event(new ItemChanged($item, 'deleted'));

        $this->dispatch('items-updated');
    }
}
