<?php

namespace App\Livewire\Forms;

use App\Enums\Assessment;
use App\Enums\TestingMethod;
use App\Models\Review;
use App\Models\ReviewItem;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ReviewItemForm extends Form
{
    public ?ReviewItem $reviewItem;

    #[Validate('required|string|max:3')]
    public string $guideline_id;
    #[Validate('required')]
    public Assessment $assessment;
    #[Validate('string|max:255')]
    public string $target = '';
    #[Validate('string')]
    public string $description;
    #[Validate]
    public ?TestingMethod $testing_method;
    #[Validate('string')]
    public string $recommendation;
    #[Validate('string')]
    public string $image_links;
    #[Validate('boolean')]
    public bool $content_issue;

    public function setModel(ReviewItem $reviewItem): void
    {
        $this->reviewItem = $reviewItem;
        $this->guideline_id = $reviewItem->guideline_id;
        $this->assessment = $reviewItem->assessment;
        $this->target = $reviewItem->target;
        $this->description = $reviewItem->description;
        $this->testing_method = $reviewItem->testing_method;
        $this->recommendation = $reviewItem->recommendation;
        $this->image_links = $reviewItem->image_links;
        $this->content_issue = $reviewItem->content_issue;
    }

    public function getModel(): ReviewItem
    {
        return $this->reviewItem;
    }

    public function store(Review $review): void
    {
        $this->validate();

        $review->reviewItems()->create($this->all());
    }

    public function update(?string $field): void
    {
        $this->validate();

        if ($field) {
            $this->reviewItem->update([$field => $this->$field]);
        } else {
            $this->reviewItem->update($this->all());
        }
    }
}
