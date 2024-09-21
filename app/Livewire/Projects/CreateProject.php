<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateProject extends Component
{
    public ProjectForm $form;

    public function save()
    {
        $this->authorize('create', Project::class);
        $project = $this->form->store();

        return redirect()->route('project.show', $project);
    }
}
