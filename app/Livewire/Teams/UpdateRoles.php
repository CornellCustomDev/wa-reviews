<?php

namespace App\Livewire\Teams;

use App\Enums\Roles;
use App\Livewire\Forms\RolesForm;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Livewire\Component;

class UpdateRoles extends Component
{
    public RolesForm $form;

    public function getRoles()
    {
        return Role::whereIn('name', Roles::getTeamRoles())->get();
    }

    public function mount(Team $team, User $user)
    {
        $this->form->setModel($team, $user);
    }

    public function save()
    {
        $this->authorize('manage-team', $this->form->team);
        $this->form->update();

        $this->dispatch('close-edit-user');
        $this->dispatch('team-changes');
    }
}
