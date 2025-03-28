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
    public $teams;

    public  function mount()
    {
        $this->teams = auth()->user()->getManagedTeams()
            ->mapWithKeys(fn ($team) => [$team->name => [
                'value' => $team->id,
                'option' => $team->name,
            ]]);
    }

    public function save()
    {
        $this->authorize('create', Project::class);
        if ($this->teams->count() === 1) {
            $this->form->team_id = $this->teams->first()['value'];
        }
        $project = $this->form->store();

        return redirect()->route('project.show', $project);
    }
}
