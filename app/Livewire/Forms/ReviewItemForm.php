<?php

namespace App\Livewire\Forms;

use App\Enums\Assessment;
use App\Enums\TestingMethod;
use App\Models\Guideline;
use App\Models\Review;
use App\Models\ReviewItem;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Form;

class ReviewItemForm extends Form
{
    public ?ReviewItem $reviewItem;

    #[Validate('required')]
    public ?int $guideline_id = null;
    #[Validate('required')]
    public Assessment $assessment = Assessment::Fail;
    #[Validate('string|nullable|max:255')]
    public ?string $description;
    #[Validate('nullable')]
    public ?TestingMethod $testing_method;
    #[Validate('string|nullable')]
    public ?string $recommendation;
    #[Validate('string|nullable')]
    public ?string $image_links;
    #[Validate('boolean|nullable')]
    public ?bool $content_issue = false;

    public Collection $guidelines;
    public Collection $guidelineOptions;
    public Collection $assessmentOptions;
    public Collection $testingMethodOptions;

    public function __construct(
        protected Component $component,
        protected $propertyName
    )
    {
        parent::__construct($component, $this->propertyName);

        $this->guidelines = Guideline::all()->keyBy('number');
        $this->guidelineOptions = $this->guidelines
            ->map(fn ($guideline) => [
                'value' => $guideline->id,
                'option' => "$guideline->number: $guideline->name",
            ]);

        $this->assessmentOptions = collect(Assessment::cases())
            ->map(fn ($assessment) => [
                'value' => $assessment->value(),
                'label' => $assessment->value(),
            ]);

        $this->testingMethodOptions = collect(TestingMethod::cases())
            ->map(fn ($test_method) => [
                'value' => $test_method->value(),
                'option' => $test_method->value(),
            ]);
    }

    public function setModel(ReviewItem $reviewItem): void
    {
        $this->reviewItem = $reviewItem;
        $this->guideline_id = $reviewItem->guideline_id;
        $this->assessment = $reviewItem->assessment;
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

    public function update(?string $field = null): void
    {
        $this->validate();

        if ($field) {
            $this->reviewItem->update([$field => $this->$field]);
        } else {
            $this->reviewItem->update($this->all());
        }
    }
}
