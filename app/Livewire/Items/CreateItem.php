<?php

namespace App\Livewire\Items;

use App\Livewire\Forms\ItemForm;
use App\Models\Issue;
use Livewire\Component;

class CreateItem extends Component
{
    public ItemForm $form;
    public Issue $issue;

    public function save()
    {
        $this->authorize('update', $this->issue->project);
        $this->form->store($this->issue);

        return redirect()->route('issues.show', [$this->issue->project, $this->issue]);
    }

    public function render()
    {
        return view('livewire.items.create-item', [
            'issue' => $this->issue,
            'guidelineOptions' => $this->form->guidelineOptions,
            'guidelines' => $this->form->guidelines,
            'assessmentOptions' => $this->form->assessmentOptions,
            'testingMethodOptions' => $this->form->testingMethodOptions
        ])->layout('components.layouts.app');
    }
}
