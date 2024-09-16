<?php

namespace App\Livewire\Reviews;

use App\Livewire\Forms\ReviewForm;
use App\Models\Project;
use App\Models\Review;
use Livewire\Component;

class CreateReview extends Component
{
    public ReviewForm $form;
    public Project $project;

    public function save()
    {
        $this->authorize('create', [Review::class, $this->project]);
        $review = $this->form->store($this->project);

        return redirect()->route('reviews.show', [$this->project, $review]);
    }

    public function render()
    {
        return view('livewire.reviews.create-review')
            ->layout('components.layouts.app');
    }
}
