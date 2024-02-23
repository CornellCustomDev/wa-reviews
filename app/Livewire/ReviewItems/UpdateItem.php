<?php

namespace App\Livewire\ReviewItems;

use App\Livewire\Forms\ReviewItemForm;
use App\Models\Review;
use App\Models\ReviewItem;
use Livewire\Component;

class UpdateItem extends Component
{
    public ReviewItemForm $form;
    public Review $review;

    public function mount(ReviewItem $reviewItem)
    {
        $this->form->setModel($reviewItem);
        $this->review = $reviewItem->review;
    }

    public function save()
    {
        $this->authorize('update', $this->review->project);
        $this->form->update();

        return redirect()->route('reviews.show', [$this->review->project, $this->review]);
    }

    public function render()
    {
        return view('livewire.review-items.update-item', [
            'review' => $this->review,
        ])->layout('components.layouts.app');
    }
}
