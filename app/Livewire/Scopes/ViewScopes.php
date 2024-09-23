<?php

namespace App\Livewire\Scopes;

use App\Models\Project;
use App\Models\Scope;
use Livewire\Component;

class ViewScopes extends Component
{
    public Project $project;

    public function render()
    {
        return view('livewire.scopes.view-scopes', [
            'scopes' => $this->project->scopes,
        ]);
    }

    public function delete(Scope $scope): void
    {
        $this->authorize('update', $scope->project);
        $scope->delete();
    }
}
