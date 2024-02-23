<?php

namespace App\Livewire\ReviewItems;

use App\Livewire\Forms\ReviewItemForm;
use App\Models\Review;
use Livewire\Component;

class CreateItem extends Component
{
    public ReviewItemForm $form;
    public Review $review;

    public function save()
    {
        $this->authorize('update', $this->review->project);
        $this->form->store($this->review);

        return redirect()->route('reviews.show', [$this->review->project, $this->review]);
    }

    public function render()
    {
        return view('livewire.review-items.create-item', [
            'review' => $this->review,
            'guidelineOptions' => $this->form->guidelineOptions,
            'guidelines' => $this->form->guidelines,
            'assessmentOptions' => $this->form->assessmentOptions,
            'testingMethodOptions' => $this->form->testingMethodOptions
        ])->layout('components.layouts.app');
    }
}
