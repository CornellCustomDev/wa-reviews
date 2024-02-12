<?php

namespace App\Livewire\ReviewItems;

use App\Livewire\Forms\ReviewItemForm;
use App\Models\ReviewItem;
use Livewire\Component;

class ReviewItemField extends Component
{
    public ReviewItemForm $form;
    public ReviewItem $reviewItem;
    public string $field;
    public string $label;

    public bool $initialized = false;
    public bool $editing = false;

    public function edit(): void
    {
        $this->authorize('update', $this->reviewItem->review);

        if (! $this->initialized) {
            $this->form->setModel($this->reviewItem);
        }
        $this->editing = true;
    }

    public function save(): void
    {
        $this->authorize('update', $this->reviewItem->review);

        $this->form->update($this->field);

        $this->reviewItem = $this->form->getModel();

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
