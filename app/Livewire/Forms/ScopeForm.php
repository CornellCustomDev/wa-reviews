<?php

namespace App\Livewire\Forms;

use App\Models\Scope;
use App\Models\Project;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ScopeForm extends Form
{
    public ?Scope $scope;

    #[Validate('required|string|max:255')]
    public string $title = '';
    #[Validate('url|max:255')]
    public string $url = '';
    #[Validate('string')]
    public string $notes = '';

    public function setModel(Scope $scope): void
    {
        $this->scope = $scope;
        $this->title = $scope->title;
        $this->url = $scope->url;
        $this->notes = $scope->notes;
    }

    public function getModel(): Scope
    {
        return $this->scope;
    }

    public function store(Project $project): Scope
    {
        $this->validate();

        $this->scope = $project->scopes()->create($this->all());

        return $this->scope;
    }

    public function update(?string $field = null): void
    {
        $this->validate();

        if ($field) {
            $this->scope->update([$field => $this->$field]);
        } else {
            $this->scope->update($this->all());
        }
    }
}
