<?php

namespace App\Livewire\Forms;

use App\Models\Team;
use App\Models\User;
use Livewire\Attributes\Validate;
use Livewire\Form;

class RolesForm extends Form
{
    public Team $team;
    public User $user;

    public string $name = '';
    #[Validate('nullable')]
    public array $roles = [];

    public function setModel(Team $team, User $user): void
    {
        $this->team = $team;
        $this->user = $user;
        $this->name = $user->name;
        $this->roles = $team->getUserRoles($user)->pluck('id')->toArray();
    }

    public function update(): void
    {
        $this->validate();

        $this->team->setUserRoles($this->user, $this->roles);
    }
}
