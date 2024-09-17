<?php

namespace App\Livewire\Items;

use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
use App\Models\Item;
use Livewire\Component;

class UpdateItem extends Component
{
    public ItemForm $form;
    public Issue $issue;

    public function mount(Item $item)
    {
        $this->form->setModel($item);
        $this->issue = $item->issue;
    }

    public function save()
    {
        $this->authorize('update', $this->issue->project);
        $this->form->update();

        return redirect()->route('issues.show', [$this->issue->project, $this->issue]);
    }

    public function render()
    {
        return view('livewire.items.update-item', [
            'issue' => $this->issue,
        ])->layout('components.layouts.app');
    }
}
