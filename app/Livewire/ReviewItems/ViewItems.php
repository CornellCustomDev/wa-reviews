<?php

namespace App\Livewire\ReviewItems;

use App\Models\Review;
use App\Models\ReviewItem;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ViewItems extends Component
{
    public Review $review;

    public function add(): void
    {
        $this->authorize('create', $this->review);

    }

    public function delete(ReviewItem $reviewItem): void
    {
        $this->authorize('delete', $this->review);

        $reviewItem->delete();
    }

    public function render(): View
    {
        return view('livewire.review-items.view-items', [
                'reviewItems' => $this->review->reviewItems,
            ]);
    }
}
