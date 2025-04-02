<?php

namespace App\Livewire\Teams;

use App\Events\UserChanged;
use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageTeamUsers extends Component
{
    public Team $team;
    public ?User $editUser = null;

    public function isTeamAdmin(User $user): bool
    {
        return $this->team->isTeamAdmin($user);
    }

    public function isReviewer(User $user): bool
    {
        return $this->team->isReviewer($user);
    }

    public function edit(User $user): void
    {
        $this->editUser = $user;
        $this->modal('edit-user')->show();
    }

    #[On('close-add-user')]
    public function closeAddUser(): void
    {
        $this->modal('add-user')->close();
        $this->dispatch('reset-add-user');
    }

    #[On('close-edit-user')]
    public function closeEditUser(): void
    {
        $this->modal('edit-user')->close();
        $this->editUser = null;
    }

    public function remove(User $user): void
    {
        $this->authorize('manageTeam', $this->team);

        $this->team->removeUser($user);
        event(new UserChanged($user, $this->team, 'removed'));

        $this->dispatch('team-changes');
    }

    public function render()
    {
        return view('livewire.teams.manage-team-users', [
            'roles' => Role::all(),
            'users' => $this->team->users,
        ]);
    }
}
