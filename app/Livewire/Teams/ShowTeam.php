<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowTeam extends Component
{
    public Team $team;
    #[Url]
    public string $tab = 'projects';
}
