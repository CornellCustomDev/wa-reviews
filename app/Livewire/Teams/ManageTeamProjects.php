<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Component;

class ManageTeamProjects extends Component
{
    public Team $team;
    public $projects = [];

    public function mount(Team $team)
    {
        $this->team = $team;
        $this->projects = $team->projects;
    }
}
