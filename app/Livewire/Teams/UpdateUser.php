<?php

namespace App\Livewire\Teams;

use App\Livewire\Forms\UserForm;
use App\Models\Team;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class UpdateUser extends Component
{
    public UserForm $form;

    #[Computed]
    public function getTeams()
    {
        return Team::all();
    }

    public function mount(User $user)
    {
        $this->form->setModel($user);
    }

    public function save()
    {
        $this->authorize('update', $this->form->user);
        $this->form->update();

        $this->dispatch('close-edit-user');
        $this->dispatch('user-changes');
    }

    #[On('close-edit-user')]
    public function closeEditUser(): void
    {
        $this->form->reset();
        $this->resetValidation();
    }
}
