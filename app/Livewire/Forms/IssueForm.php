<?php

namespace App\Livewire\Forms;

use App\Models\Issue;
use App\Models\Scope;
use Livewire\Attributes\Validate;
use Livewire\Form;

class IssueForm extends Form
{
    public ?Issue $issue;

    #[Validate('required|string|max:255')]
    public string $target = '';
    #[Validate('string')]
    public string $description = '';
    #[Validate('string')]
    public string $recommendation = '';

    public function setModel(Issue $issue): void
    {
        $this->issue = $issue;
        $this->target = $issue->target;
        $this->description = $issue->description;
        $this->recommendation = $issue->recommendation;
    }

    public function getModel(): Issue
    {
        return $this->issue;
    }

    public function store(Scope $scope): Issue
    {
        $this->validate();

        $attributes = array_merge($this->all(), ['project_id' => $scope->project_id]);
        $this->issue = $scope->issues()->create($attributes);

        return $this->issue;
    }

    public function update(?string $field = null): void
    {
        $this->validate();

        if ($field) {
            $this->issue->update([$field => $this->$field]);
        } else {
            $this->issue->update($this->all());
        }
    }
}
