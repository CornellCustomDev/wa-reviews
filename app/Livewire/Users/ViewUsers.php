<?php

namespace App\Livewire\Users;

use App\Models\Team;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewUsers extends Component
{
    public ?User $editUser = null;

    public function edit(User $user): void
    {
        $this->editUser = $user;
        $this->modal('edit-user')->show();
    }

    #[On('close-edit-user')]
    public function closeEditUser(): void
    {
        $this->modal('edit-user')->close();
        $this->editUser = null;
    }

    public function delete(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
        $this->dispatch('user-changes');
    }

    #[On('team-changes')]
    public function refreshUsers()
    {
        $this->render();
    }

    public function render()
    {
        return view('livewire.users.view-users', [
            'users' => User::all(),
            'teams' => Team::all(),
        ]);
    }
}
