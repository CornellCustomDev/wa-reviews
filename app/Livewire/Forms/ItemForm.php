<?php

namespace App\Livewire\Forms;

use App\Enums\Assessment;
use App\Enums\Impact;
use App\Enums\TestingMethod;
use App\Events\ItemChanged;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class ItemForm extends Form
{
    public ?Item $item;

    #[Validate('required')]
    public $guideline_id = '';
    #[Validate('required')]
    public Assessment $assessment;
    #[Validate('string|nullable|max:255')]
    public ?string $description;
    #[Validate('nullable')]
    public ?TestingMethod $testing_method;
    #[Validate('string|nullable')]
    public ?string $recommendation;
    #[Validate('string|nullable')]
    public ?string $testing = '';
    public ?array $image_links = [];
    public array $imagesToRemove = [];
    #[Validate(['images.*' => 'nullable|image'])]
    public array $images = [];
    #[Validate('boolean|nullable')]
    public ?bool $content_issue = false;
    public ?Impact $impact;

    public Collection $guidelines;
    public Collection $guidelineOptions;
    public Collection $assessmentOptions;
    public Collection $testingMethodOptions;
    public Collection $impactOptions;

    private $storage;

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

        $this->impactOptions = collect(Impact::cases())
            ->map(fn ($impact) => [
                'value' => $impact->value(),
                'label' => $impact->value(),
                'description' => $impact->getDescription(),
            ]);

        $this->storage = Storage::disk('public');
    }

    public function getOptions($field): array
    {
        return match($field) {
            'guideline_id' => $this->guidelineOptions->toArray(),
            'assessment' => $this->assessmentOptions->toArray(),
            'impact' => $this->impactOptions->toArray(),
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
        $this->images = [];
        $this->content_issue = $item->content_issue;
        $this->impact = $item->impact;
    }

    public function getModel(): Item
    {
        return $this->item;
    }

    public function store(Issue $issue): void
    {
        $this->validate();

        $item = $issue->items()->create($this->all());

        $image_links = [];
        /** @var TemporaryUploadedFile $file */
        foreach ($this->images as $file) {
            $storedFilename = $file->storeAs("issues/$issue->id/$item->id", $file->getClientOriginalName(), 'public');
            $image_links[] = $this->storage->url($storedFilename);
        }
        $item->image_links = $image_links;
        $item->save();

        event(new ItemChanged($item, 'created'));
    }

    public function removeExistingImage(string $filename): void
    {
        $path = "issues/{$this->item->issue->id}/{$this->item->id}";
        if (!$this->storage->exists("$path/$filename")) {
            return;
        }

        $url = $this->storage->url("$path/$filename");
        $this->image_links = array_diff($this->image_links, [$url]);
        $this->imagesToRemove[] = $filename;
    }

    public function update(?string $field = null): void
    {
        $this->validate();

        if ($field) {
            $this->item->update([$field => $this->$field]);
            return;
        }

        $path = "issues/{$this->item->issue->id}/{$this->item->id}";
        foreach ($this->imagesToRemove as $filename) {
            $this->storage->delete("$path/$filename");
            if (empty($this->storage->allFiles($path))) {
                $this->storage->deleteDirectory($path);
            }
            $issuePath = dirname($path);
            if (empty($this->storage->allFiles($issuePath))) {
                $this->storage->deleteDirectory($issuePath);
            }
        }

        $attributes = $this->all();
        /** @var TemporaryUploadedFile $file */
        foreach ($this->images as $file) {
            $originalName = $file->getClientOriginalName();
            if ($this->isDuplicate($originalName, $file)) {
                continue;
            }
            $storedFilename = $file->storeAs($path, $originalName, 'public');
            $attributes['image_links'][] = $this->storage->url($storedFilename);
        }

        $this->item->update($attributes);

        event(new ItemChanged($this->item, 'updated'));
    }

    private function isDuplicate(string $originalName, TemporaryUploadedFile $file): bool
    {
        if (empty($this->image_links)) {
            return false;
        }

        $tempFilepath = $file->getRealPath();
        $path = "issues/{$this->item->issue->id}/{$this->item->id}";

        return collect($this->image_links)
            ->map(fn ($url) => basename($url))
            ->filter(fn ($filename) => $filename === $originalName)
            ->filter(fn ($filename) => md5_file($tempFilepath) === md5_file($this->storage->path("$path/$filename")))
            ->isNotEmpty();
    }
}
