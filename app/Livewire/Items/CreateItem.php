<?php

namespace App\Livewire\Items;

use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app', ['sidebar' => true])]
class CreateItem extends Component
{
    public ItemForm $form;
    public Issue $issue;

    public function save()
    {
        $this->authorize('update', $this->issue->project);
        $this->form->store($this->issue);

        return redirect()->route('issue.show', $this->issue);
    }
}
