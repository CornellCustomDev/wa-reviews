<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewTeams extends Component
{
    public bool $createTeam;
    public ?Team $editTeam;

    public function create(): void
    {
        $this->createTeam = true;
        $this->modal('edit-team')->show();
    }

    public function edit(Team $team): void
    {
        $this->editTeam = $team;
        $this->modal('edit-team')->show();
    }

    #[On(['close-edit-team'])]
    public function closeEditTeam(): void
    {
        $this->modal('edit-team')->close();
        $this->editTeam = null;
        $this->createTeam = false;
    }

    public function delete(Team $team)
    {
        $this->authorize('delete', $team);
        $team->delete();
        $this->dispatch('team-changes');
    }

    public function render()
    {
        return view('livewire.teams.view-teams', [
            'teams' => auth()->user()->teams
        ]);
    }
}
