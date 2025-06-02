<?php

namespace App\Livewire\Forms;

use App\Events\IssueChanged;
use App\Models\Issue;
use App\Models\Project;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Livewire\Attributes\Validate;
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

        event(new IssueChanged($this->issue, 'created'));

        if ($this->generateGuidelines) {
            app(GuidelinesAnalyzerServiceInterface::class)->populateIssueItemsWithAI($this->issue);
        }

        return $this->issue;
    }

}
