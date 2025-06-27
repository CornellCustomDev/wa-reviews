<?php

namespace App\Livewire\Forms;

use App\Enums\AIStatus;
use App\Enums\Assessment;
use App\Enums\Impact;
use App\Enums\TestingMethod;
use App\Events\IssueChanged;
use App\Models\Guideline;
use App\Models\Issue;
use App\Models\Item;
use App\Models\Project;
use App\Models\SiaRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

class IssueForm extends Form
{
    public ?Issue $issue;

    #[Validate('nullable')]
    public $scope_id = null;
    #[Validate('required|string|max:255')]
    public string $target = '';
    #[Validate('string')]
    public string $description = '';

    #[Validate('nullable')]
    public $guideline_id = null;
    #[Validate('nullable')]
    public ?Assessment $assessment;
    #[Validate('nullable')]
    public ?TestingMethod $testing_method;
    #[Validate('string|nullable')]
    public ?string $recommendation;
    #[Validate('string|nullable')]
    public ?string $testing;
    public ?array $image_links = [];
    public array $imagesToRemove = [];
    #[Validate(['images.*' => 'nullable|image'])]
    public array $images = [];
    #[Validate('boolean|nullable')]
    public ?bool $content_issue = false;
    public ?Impact $impact;

    private $storage;

    public Collection $scopeOptions;

    public function __construct(
        protected Component $component,
        protected $propertyName
    )
    {
        parent::__construct($component, $this->propertyName);

        $this->storage = Storage::disk('public');
    }

    public static function getGuidelineSelectArray(): array
    {
        return Guideline::query()
            ->select([
                'guidelines.id',
                'guidelines.number',
                'guidelines.name',
                'guidelines.criterion_id',
                'guidelines.category_id',
            ])
            ->with(['criterion:id,number,level', 'category:id,name'])
            ->get()
            ->map(function (Guideline $guideline) {
                return [
                    'value' => $guideline->id,
                    'option' => "{$guideline->getNumber()}: {$guideline->getCriterionInfo()} - $guideline->name",
                ];
            })
            ->toArray();
    }

    public function setModel(Issue $issue): void
    {
        $this->issue = $issue;
        $this->scope_id = $issue->scope_id ?? '';
        $this->target = $issue->target;
        $this->description = $issue->description;
        $this->guideline_id = $issue->guideline_id;
        $this->assessment = $issue->assessment;
        $this->testing_method = $issue->testing_method;
        $this->recommendation = $issue->recommendation;
        $this->testing = $issue->testing;
        $this->image_links = $issue->image_links;
        $this->images = [];
        $this->content_issue = $issue->content_issue;
        $this->impact = $issue->impact;

        $this->scopeOptions = $this->issue->project->scopes
            ->map(fn($scope) => [
                'value' => $scope->id,
                'option' => $scope->title,
            ]);
    }

    public function getModel(): Issue
    {
        return $this->issue;
    }

    public function store(Project $project, ?SiaRule $rule = null, ?array $aiData = null): Issue
    {
        $this->validate();

        $attributes = array_merge($this->except('generateGuidelines'), [
            'project_id' => $project->id,
            'sia_rule_id' => $rule?->id,
        ]);
        $attributes['scope_id'] = $attributes['scope_id'] ?: null;
        $attributes['guideline_id'] = $attributes['guideline_id'] ?: null;
        if ($aiData) {
            $attributes['ai_reasoning'] = $aiData['ai_reasoning'];
            $attributes['ai_status'] = AIStatus::Accepted;
        }

        $this->issue = $project->issues()->create($attributes);

        $image_links = [];
        /** @var TemporaryUploadedFile $file */
        foreach ($this->images as $file) {
            $storedFilename = $file->storeAs("issues/{$this->issue->id}", $file->getClientOriginalName(), 'public');
            $image_links[] = $this->storage->url($storedFilename);
        }
        $this->issue->image_links = $image_links;
        $this->issue->save();

        event(new IssueChanged($this->issue, 'created'));

        if ($aiData) {
            Item::create([
                'issue_id' => $this->issue->id,
                ...$aiData,
            ]);
        }

        return $this->issue;
    }

    public function update(): void
    {
        $this->validate();

        $path = "issues/{$this->issue->id}";
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
        $attributes['scope_id'] = $attributes['scope_id'] ?: null;
        $attributes['ai_status'] = AIStatus::Modified;

        /** @var TemporaryUploadedFile $file */
        foreach ($this->images as $file) {
            $originalName = $file->getClientOriginalName();
            if ($this->isDuplicate($originalName, $file)) {
                continue;
            }
            $storedFilename = $file->storeAs($path, $originalName, 'public');
            $attributes['image_links'][] = $this->storage->url($storedFilename);
        }
        $this->issue->update($attributes);

        event(new IssueChanged($this->issue, 'updated'));
    }

    public function removeExistingImage(string $filename): void
    {
        $path = "issues/{$this->issue->id}";
        if (!$this->storage->exists("$path/$filename")) {
            return;
        }

        $url = $this->storage->url("$path/$filename");
        $this->image_links = array_diff($this->image_links, [$url]);
        $this->imagesToRemove[] = $filename;
    }

    private function isDuplicate(string $originalName, TemporaryUploadedFile $file): bool
    {
        if (empty($this->image_links)) {
            return false;
        }
        $tempFilepath = $file->getRealPath();
        $path = "issues/{$this->issue->id}";

        return collect($this->image_links)
            ->map(fn ($url) => basename($url))
            ->filter(fn ($filename) => $filename === $originalName)
            ->filter(fn ($filename) => md5_file($tempFilepath) === md5_file($this->storage->path("$path/$filename")))
            ->isNotEmpty();
    }
}
