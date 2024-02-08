<?php

namespace App\Livewire\Reviews;

use App\Models\Project;
use App\Models\Review;
use Livewire\Component;

class ViewReviews extends Component
{
    public Project $project;

    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.reviews.view-reviews', [
                'reviews' => $this->project->reviews,
            ])
            ->layout('components.layouts.app', ['sidebar' => false]);
    }

    public function delete(Review $review)
    {
        $this->authorize('delete', $review);

        $review->delete();
    }
}
