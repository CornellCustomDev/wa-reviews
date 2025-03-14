<?php

namespace App\Livewire\Users;

use App\Models\Team;
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
    }

    public function render()
    {
        return view('livewire.users.view-teams', [
            'teams' => Team::all(),
        ]);
    }
}
