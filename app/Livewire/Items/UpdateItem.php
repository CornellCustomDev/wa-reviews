<?php

namespace App\Livewire\Items;

use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
use App\Models\Item;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateItem extends Component
{
    public ItemForm $form;
    public Issue $issue;

    public function mount(Issue $issue, Item $item)
    {
        $this->issue = $issue;
        $this->form->setModel($item);
    }

    public function save()
    {
        $this->authorize('update', $this->form->item->issue);
        $this->form->update();

        return redirect()->route('issue.show', $this->form->item->issue);
    }
}
