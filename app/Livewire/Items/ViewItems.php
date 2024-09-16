<?php

namespace App\Livewire\Items;

use App\Models\Issue;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewItems extends Component
{
    public Issue $issue;

    public function add(): void
    {
        $this->authorize('create', $this->issue->project);

    }

    public function delete(Item $item): void
    {
        $this->authorize('delete', $this->issue);

        $item->delete();
    }

    public function render(): View
    {
        return view('livewire.items.view-items', [
            'items' => $this->issue->items,
        ]);
    }
}
