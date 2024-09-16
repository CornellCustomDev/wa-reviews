<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use Livewire\Component;

class IssueField extends Component
{
    public IssueForm $form;
    public Issue $issue;
    public string $field;
    public string $label;

    public bool $initialized = false;
    public bool $editing = false;

    public function edit(): void
    {
        $this->authorize('update', $this->issue);

        if (! $this->initialized) {
            $this->form->setModel($this->issue);
        }
        $this->editing = true;
    }

    public function save(): void
    {
        $this->authorize('update', $this->issue);

        $this->form->update($this->field);

        $this->issue = $this->form->getModel();

        $this->editing = false;
    }

    public function cancel(): void
    {
        $this->initialized = false;
        $this->editing = false;
    }

    public function close(): void
    {
        $this->editing = false;
    }
}
