<?php

namespace App\Livewire\Forms;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Scope;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Form;

class ProjectIssueForm extends Form
{
    public ?Issue $issue;

    #[Validate('nullable')]
    public $scope_id = '';
    #[Validate('required|string|max:255')]
    public string $target = '';
    #[Validate('string')]
    public string $description = '';
    #[Validate('string')]
    public string $recommendation = '';
    public bool $generateGuidelines = false;

    public Collection $scopeOptions;

    public function __construct(
        protected Component $component,
        protected $propertyName
    )
    {
        parent::__construct($component, $this->propertyName);

        if (isset($this->component->project)) {
            $this->scopeOptions = Scope::where('project_id', $this->component->project->id)
                ->get()
                ->map(fn($scope) => [
                    'value' => $scope->id,
                    'option' => $scope->title,
                ]);
        } else {
            $this->scopeOptions = collect();
        }
    }

    public function setModel(Issue $issue): void
    {
        $this->issue = $issue;
        $this->scope_id = $issue->scope_id ?? '';
        $this->target = $issue->target;
        $this->description = $issue->description;
        $this->recommendation = $issue->recommendation;
    }

    public function getModel(): Issue
    {
        return $this->issue;
    }

    public function store(Project $project): Issue
    {
        $this->validate();

        $attributes = $this->except('generateGuidelines');
        $attributes['scope_id'] = $attributes['scope_id'] ?: null;
        $this->issue = $project->issues()->create($attributes);

        if ($this->generateGuidelines) {
            GuidelinesAnalyzerService::populateIssueItemsWithAI($this->issue);
        }

        return $this->issue;
    }

}
