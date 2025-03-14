<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;

class ViewUsers extends Component
{
    public function delete(User $user)
    {
        $this->authorize('delete', $user);
        $user->delete();
    }

    public function render()
    {
        return view('livewire.users.view-users', [
            'users' => User::all(),
        ]);
    }
}
