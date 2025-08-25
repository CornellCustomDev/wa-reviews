<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateProject extends Component
{
    public ProjectForm $form;
    public array $teams;

    public function mount(Project $project)
    {
        $this->form->setModel($project);

        $this->teams = $this->form->getTeamSelectArray();
    }

    public function save()
    {
        $this->authorize('update', $this->form->project);
        $this->form->update();

        return redirect()->route('project.show', $this->form->project);
    }
}
