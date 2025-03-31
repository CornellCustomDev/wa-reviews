<?php

namespace App\Livewire\Forms;

use App\Events\UserChanged;
use App\Models\Role;
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

        event(new UserChanged($this->user, $this->team, 'roles updated', [
            'user_name' => $this->user->name,
            'roles' => Role::find($this->roles)->pluck('display_name')->join(', '),
        ]));
    }
}
