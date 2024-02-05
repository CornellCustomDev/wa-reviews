<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use App\Models\Project;
use Livewire\Component;

class CreateProject extends Component
{
    public ProjectForm $form;

    public function save()
    {
        $this->authorize('create', Project::class);
        $this->form->store();

        return redirect()->route('projects.index');
    }

    public function render()
    {
        return view('livewire.projects.create-project')
            ->layout('components.layouts.app');
    }
}
