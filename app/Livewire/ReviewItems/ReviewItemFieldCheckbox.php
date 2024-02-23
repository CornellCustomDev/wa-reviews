<?php

namespace App\Livewire\ReviewItems;

use App\Models\ReviewItem;

class ReviewItemFieldCheckbox extends ReviewItemField
{
    public function mount(ReviewItem $reviewItem): void
    {
        $this->reviewItem = $reviewItem;
        $this->form->setModel($this->reviewItem);
    }
}
