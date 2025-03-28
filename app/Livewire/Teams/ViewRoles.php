<?php

namespace App\Livewire\Teams;

use App\Models\Role;
use Livewire\Component;

class ViewRoles extends Component
{
    public function render()
    {
        return view('livewire.teams.view-roles', [
            'roles' => Role::all(),
        ]);
    }
}
