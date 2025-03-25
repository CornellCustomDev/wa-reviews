<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use App\Models\Project;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateProject extends Component
{
    public ProjectForm $form;

    public function getTeams()
    {
        return auth()->user()->teams
            ->mapWithKeys(fn ($team) => [$team->name => [
                'value' => $team->id,
                'option' => $team->name,
            ]]);
    }

    public function save()
    {
        $this->authorize('create', Project::class);
        $project = $this->form->store();

        return redirect()->route('project.show', $project);
    }
}
