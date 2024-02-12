<?php

namespace App\Livewire\Reviews;

use App\Livewire\Forms\ReviewForm;
use App\Models\Review;
use Livewire\Component;

class ReviewField extends Component
{
    public ReviewForm $form;
    public Review $review;
    public string $field;
    public string $label;

    public bool $initialized = false;
    public bool $editing = false;

    public function edit(): void
    {
        $this->authorize('update', $this->review);

        if (! $this->initialized) {
            $this->form->setModel($this->review);
        }
        $this->editing = true;
    }

    public function save(): void
    {
        $this->authorize('update', $this->review);

        $this->form->update($this->field);

        $this->review = $this->form->getModel();

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
