<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use App\Models\Project;
use Livewire\Component;

class UpdateProject extends Component
{
    public ProjectForm $form;

    public function mount(Project $project)
    {
        $this->form->setModel($project);
    }

    public function save()
    {
        $this->form->update();

        return redirect()->route('projects.index');
    }

    public function render()
    {
        return view('livewire.projects.update-project');
    }
}
