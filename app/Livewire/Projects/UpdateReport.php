<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ReportForm;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateReport extends Component
{
    public ReportForm $form;

    public function mount(Project $project)
    {
        $this->form->setModel($project);
    }

    public function save()
    {
        $this->authorize('update', $this->form->project);
        $this->form->update();

        return redirect()->route('project.show', ['project' => $this->form->project, 'tab' => 'report']);
    }
}
