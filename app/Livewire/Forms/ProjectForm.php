<?php

namespace App\Livewire\Forms;

use App\Models\Project;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProjectForm extends Form
{
    public ?Project $project;

    #[Validate('required|string|max:255')]
    public string $name = '';
    #[Validate('required')]
    #[Validate('url', message: 'The Site URL must be a valid URL')]
    public string $site_url = '';
    #[Validate('nullable|string')]
    public string $description = '';

    public function setModel(Project $project): void
    {
        $this->project = $project;
        $this->name = $project->name;
        $this->site_url = $project->site_url;
        $this->description = $project->description;
    }

    public function store(): void
    {
        $this->validate();

        Project::create($this->all());
    }

    public function update(): void
    {
        $this->validate();

        $this->project->update($this->all());
    }
}
