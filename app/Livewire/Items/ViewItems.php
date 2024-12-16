<?php

namespace App\Livewire\Items;

use App\Models\Issue;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ViewItems extends Component
{
    public Issue $issue;
    public ?Item $editItem;

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

    public function delete(Item $item): void
    {
        $this->authorize('delete', $this->issue);
        $item->delete();

        $this->dispatch('items-updated');
    }
}
