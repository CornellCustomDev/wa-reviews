<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Models\User;
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
            ],
        ]);

        // Get the user
        $user = User::find($validated['user']);

        // Add the user to the team as a member
        $this->team->users()->attach($user);
        $user->syncRoles(['member'], $this->team->id);

        $this->dispatch('close-add-user');
    }

    #[On('close-add-user')]
    public function closeAddUser(): void
    {
        $this->user = null;
        $this->resetValidation();
        $this->teamChanges();
    }
}
