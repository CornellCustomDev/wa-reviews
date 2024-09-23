<?php

namespace App\Livewire\Items;

use App\Models\Issue;
use App\Models\Item;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewItems extends Component
{
    public Issue $issue;

    #[On('items-updated')]
    public function refreshIssue(): void
    {
        $this->issue->refresh();
    }

    public function delete(Item $item): void
    {
        $this->authorize('delete', $this->issue);
        $item->delete();

        $this->dispatch('items-updated');
    }
}
