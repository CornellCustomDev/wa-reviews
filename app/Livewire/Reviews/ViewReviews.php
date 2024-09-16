<?php

namespace App\Livewire\Reviews;

use App\Livewire\Forms\ReviewForm;
use App\Models\Project;
use App\Models\Review;
use Livewire\Component;

class ViewReviews extends Component
{
    public ReviewForm $form;
    public Project $project;
    public int $editingId = 0;

    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.reviews.view-reviews', [
            'reviews' => $this->project->reviews,
        ]);
    }

    public function delete(Review $review): void
    {
        $this->authorize('delete', $review);

        $review->delete();
    }
}
