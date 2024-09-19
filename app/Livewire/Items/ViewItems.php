<?php

namespace App\Livewire\Items;

use App\Models\Issue;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewItems extends Component
{
    public Issue $issue;

    public function delete(Item $item): void
    {
        $this->authorize('delete', $this->issue);
        $item->delete();

        $this->dispatch('issues-updated');
    }

    public function render(): View
    {
        return view('livewire.items.view-items', [
            'items' => $this->issue->items,
        ]);
    }
}
