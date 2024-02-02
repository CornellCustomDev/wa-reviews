<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use Livewire\Component;

class CreateProject extends Component
{
    public ProjectForm $form;

    public function save()
    {
        $this->form->store();

        return redirect()->route('projects.index');
    }

    public function render()
    {
        return view('livewire.projects.create-project');
    }
}
