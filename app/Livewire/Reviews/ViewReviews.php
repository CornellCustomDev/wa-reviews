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
            ])
            ->layout('components.layouts.app', [
                'sidebar' => false,
                'breadcrumbs' => [
                    'Projects' => route('projects.index'),
                    $this->project->name => route('projects.show', $this->project),
                    'Reviews' => 'active'
                ],
            ]);
    }

    public function delete(Review $review): void
    {
        $this->authorize('delete', $review);

        $review->delete();
    }
}
