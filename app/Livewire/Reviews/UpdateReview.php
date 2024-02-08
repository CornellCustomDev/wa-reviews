<?php

namespace App\Livewire\Reviews;

use App\Livewire\Forms\ReviewForm;
use App\Models\Project;
use App\Models\Review;
use Livewire\Component;

class UpdateReview extends Component
{
    public ReviewForm $form;
    public Project $project;

    public function mount(Project $project, Review $review)
    {
        $this->project = $project;
        $this->form->setModel($review);
    }

    public function save()
    {
        $this->authorize('update', $this->form->review);
        $this->form->update();

        return redirect()->route('reviews.show', [$this->project, $this->form->review]);
    }

    public function render()
    {
        return view('livewire.reviews.update-review');
    }
}
