<?php

namespace App\Livewire\Scopes;

use App\Livewire\Forms\ScopeForm;
use App\Models\Project;
use App\Models\Scope;
use Livewire\Component;

class AddScope extends Component
{
    public ScopeForm $form;
    public Project $project;

    public function save()
    {
        $this->authorize('create', [Scope::class, $this->project]);
        $scope = $this->form->store($this->project);

        $this->dispatch('refresh-scopes', scope_id: $scope->id);
        $this->modal('add-scope')->close();
        $this->form->reset();
    }
}
