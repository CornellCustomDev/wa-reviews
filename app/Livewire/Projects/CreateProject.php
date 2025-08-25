<?php

namespace App\Livewire\Projects;

use App\Livewire\Forms\ProjectForm;
use App\Models\Team;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateProject extends Component
{
    public ProjectForm $form;
    public Team $team;
    public array $teams;

    public function mount(?Team $team): void
    {
        if ($team) {
            $this->form->team_id = $team->id;
        }

        $this->teams = $this->form->getTeamSelectArray();
    }

    public function save()
    {
        $this->authorize('create-projects', $this->team);
        $project = $this->form->store($this->team);

        return redirect()->route('project.show', $project);
    }

    public function render()
    {
        return view('livewire.projects.create-project')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            $this->team->name => route('teams.show', $this->team),
            'Add Project' => 'active'
        ];
    }
}
