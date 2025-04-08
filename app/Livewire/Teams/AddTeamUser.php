<?php

namespace App\Livewire\Teams;

use App\Models\Team;
use App\Models\User;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapData;
use CornellCustomDev\LaravelStarterKit\Ldap\LdapSearch;
use Exception;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddTeamUser extends Component
{
    public Team $team;
    public string $search = '';
    #[Validate('required', as: 'person')]
    public string $addUserEmail;

    #[Computed]
    public function nonTeamUsers(): array
    {
        // If there is a search term, search LDAP
        try {
            $searchFilter = "(|(uid=$this->search*)(displayname=*$this->search*)(mail=$this->search*))";
            $result = (Str::of($this->search)->length() > 3) ? LdapSearch::search($searchFilter) : null;
            $ldapUsers = collect($result)
                ->filter(fn ($ldapData) => $ldapData->email)
                ->mapWithKeys(fn (LdapData $ldapData) => [
                    $ldapData->principalName() => new User([
                        'name' => $ldapData->name(),
                        'email' => $ldapData->email(),
                    ])
                ]);
        } catch (Exception $e) {
            $ldapUsers = collect();
        }
        $users = User::query()
            ->whereDoesntHave('teams', fn ($query) => $query->where('team_id', $this->team->id))
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('uid', 'like', '%' . $this->search . '%');
                });
            })
            ->get()
            ->mapWithKeys(fn ($user) => [
                $user->uid => $user,
            ]);

        return $ldapUsers->merge($users)->sortBy('name')->all();
    }

    public function updatedSearch(): void
    {
        unset($this->nonTeamUsers);
    }

    #[On('team-changes')]
    public function teamChanges(): void
    {
        unset($this->nonTeamUsers);
    }

    public function save()
    {
        $this->authorize('manage-team', $this->team);

        $this->validate();

        $newTeamUser = User::firstWhere('email', $this->addUserEmail);

        // if the user isn't found, but they are in LDAP, create a new user
        if (empty($newTeamUser)) {
            $email = $this->addUserEmail;
            $ldapData = LdapSearch::getByEmail($email);
            if ($ldapData) {
                $newTeamUser = User::createUserFromLdapData($ldapData);
            } else {
                $this->addError('addUserEmail', "$this->addUserEmail not found");
                return;
            }
        }

        $this->team->addUser($newTeamUser);

        $this->dispatch('team-changes');
        $this->dispatch('close-add-user');
    }

    #[On('reset-add-user')]
    public function resetAddUser(): void
    {
        unset($this->addUserEmail);
        $this->resetValidation();
    }
}
