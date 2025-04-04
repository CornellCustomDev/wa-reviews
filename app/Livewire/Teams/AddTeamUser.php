<?php

namespace App\Livewire\Teams;

use App\Events\UserChanged;
use App\Models\Team;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AddTeamUser extends Component
{
    public Team $team;
    public $user;

    #[Computed]
    public function nonTeamUsers(): array
    {
        return User::whereDoesntHave('teams', function ($query) {
            $query->where('team_id', $this->team->id);
        })->get()->all();
    }

    #[On('team-changes')]
    public function teamChanges(): void
    {
        unset($this->nonTeamUsers);
    }

    public function save()
    {
        $this->authorize('manageTeam', $this->team);

        // Validate that the user exists and is not already on the team
        $validated = $this->validate([
            'user' => [
                'required',
                Rule::unique('team_user', 'user_id')->where('team_id', $this->team->id)
            ],
        ]);

        $user = User::find($validated['user']);
        $this->team->addUser($user);

        event(new UserChanged($user, $this->team, 'added'));

        $this->dispatch('team-changes');
        $this->dispatch('close-add-user');
    }

    #[On('reset-add-user')]
    public function resetAddUser(): void
    {
        $this->user = null;
        $this->resetValidation();
    }
}
