<?php

namespace App\Livewire\Forms;

use App\Models\Project;
use App\Models\Review;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReviewForm extends Form
{
    public ?Review $review;

    #[Validate('required|string|max:255')]
    public string $target = '';
    #[Validate('string')]
    public string $description = '';
    #[Validate('string')]
    public string $recommendation = '';

    public function setModel(Review $review): void
    {
        $this->review = $review;
        $this->target = $review->target;
        $this->description = $review->description;
        $this->recommendation = $review->recommendation;
    }

    public function getModel(): Review
    {
        return $this->review;
    }

    public function store(Project $project): void
    {
        $this->validate();

        $project->reviews()->create($this->all());
    }

    public function update(?string $field): void
    {
        $this->validate();

        if ($field) {
            $this->review->update([$field => $this->$field]);
        } else {
            $this->review->update($this->all());
        }
    }
}
