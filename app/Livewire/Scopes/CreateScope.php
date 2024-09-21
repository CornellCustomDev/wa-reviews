<?php

namespace App\Livewire\Scopes;

use App\Livewire\Forms\ScopeForm;
use App\Models\Project;
use App\Models\Scope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateScope extends Component
{
    public ScopeForm $form;
    public Project $project;

    public function save()
    {
        $this->authorize('update', $this->project);
        $this->form->store($this->project);

        return redirect()->route('project.show', $this->project);
    }
}
