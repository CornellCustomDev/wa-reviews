<?php

namespace App\Livewire\Reviews;

use App\Models\Review;
use Livewire\Component;

class ShowReview extends Component
{
    public Review $review;

    public function render()
    {
        $this->authorize('view', $this->review);
        return view('livewire.reviews.show-review')
            ->layout('components.layouts.app', ['sidebar' => false, 'breadcrumbs' => [
                'Projects' => route('projects.index'),
                $this->review->project->name => route('projects.show', $this->review->project),
                'Reviews' => route('reviews.index', $this->review->project),
                'Viewing Review' => 'active'
            ]]);
    }
}
