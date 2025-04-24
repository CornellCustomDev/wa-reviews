<?php

namespace App\Livewire\Forms;

use App\Events\IssueChanged;
use App\Models\Issue;
use App\Models\Scope;
use App\Models\SiaRule;
use App\Services\GuidelinesAnalyzer\GuidelinesAnalyzerServiceInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;
use Livewire\Form;

class IssueForm extends Form
{
    public ?Issue $issue;

    #[Validate('nullable')]
    public $scope_id = '';
    #[Validate('required|string|max:255')]
    public string $target = '';
    #[Validate('string')]
    public string $description = '';
    public bool $generateGuidelines = false;

    public Collection $scopeOptions;

    public function setModel(Issue $issue): void
    {
        $this->issue = $issue;
        $this->scope_id = $issue->scope_id ?? '';
        $this->target = $issue->target;
        $this->description = $issue->description;

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

    public function store(Scope $scope, ?SiaRule $rule = null): Issue
    {
        $this->validate();

        $attributes = array_merge($this->except('generateGuidelines'), [
            'project_id' => $scope->project_id,
            'sia_rule_id' => $rule?->id,
        ]);
        $this->issue = $scope->issues()->create($attributes);

        event(new IssueChanged($this->issue, 'created'));

        if ($this->generateGuidelines) {
            app(GuidelinesAnalyzerServiceInterface::class)->populateIssueItemsWithAI($this->issue);
        }

        return $this->issue;
    }

    public function update(): void
    {
        $this->validate();

        $attributes = $this->all();
        $attributes['scope_id'] = $attributes['scope_id'] ?: null;
        $this->issue->update($attributes);

        event(new IssueChanged($this->issue, 'updated'));
    }
}
