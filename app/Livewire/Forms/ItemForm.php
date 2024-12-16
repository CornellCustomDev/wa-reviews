<?php

namespace App\Livewire\Forms;

use App\Enums\Assessment;
use App\Enums\TestingMethod;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Form;

class ItemForm extends Form
{
    public ?Item $item;

    #[Validate('required')]
    public $guideline_id = '';
    #[Validate('required')]
    public Assessment $assessment = Assessment::Fail;
    #[Validate('string|nullable|max:255')]
    public ?string $description;
    #[Validate('nullable')]
    public ?TestingMethod $testing_method;
    #[Validate('string|nullable')]
    public ?string $recommendation;
    #[Validate('string|nullable')]
    public ?string $testing = '';
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

    public function getOptions($field): array
    {
        return match($field) {
            'guideline_id' => $this->guidelineOptions->toArray(),
            'assessment' => $this->assessmentOptions->toArray(),
            'testing_method' => $this->testingMethodOptions->toArray(),
            default => [],
        };
    }

    public function setModel(Item $item): void
    {
        $this->item = $item;
        $this->guideline_id = $item->guideline_id;
        $this->assessment = $item->assessment;
        $this->description = $item->description;
        $this->testing_method = $item->testing_method;
        $this->recommendation = $item->recommendation;
        $this->testing = $item->testing;
        $this->image_links = $item->image_links;
        $this->content_issue = $item->content_issue;
    }

    public function getModel(): Item
    {
        return $this->item;
    }

    public function store(Issue $issue): void
    {
        $this->validate();

        $issue->items()->create($this->all());
    }

    public function update(?string $field = null): void
    {
        $this->validate();

        if ($field) {
            $this->item->update([$field => $this->$field]);
        } else {
            $this->item->update($this->all());
        }
    }
}
