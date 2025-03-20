<?php

namespace App\Livewire\Users;

use App\Models\Role;
use Livewire\Component;

class ViewRoles extends Component
{
    public function render()
    {
        return view('livewire.users.view-roles', [
            'roles' => Role::all(),
        ]);
    }
}
