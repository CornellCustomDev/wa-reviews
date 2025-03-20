<?php

namespace App\Livewire\Users;

use App\Models\Team;
use Livewire\Attributes\Url;
use Livewire\Component;

class ManageTeam extends Component
{
    public Team $team;
    #[Url]
    public string $tab = 'members';
}
