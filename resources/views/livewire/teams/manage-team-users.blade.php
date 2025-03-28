<div>
    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th style="width: 120px">Reviewer</th>
            <th style="width: 120px">Team Admin</th>
            <th class="w-24">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>
                    {{ $user->name }}
                </td>
                <td>
                    {{ $user->email }}
                </td>
                <td class="text-center">
                    @if($this->isReviewer($user))
                        <flux:icon.check-circle
                            class="text-green-500"
                            variant="solid"
                        />
                    @endif
                </td>
                <td class="text-center">
                    @if($this->isTeamAdmin($user))
                        <flux:icon.check-circle
                            class="text-green-500"
                            variant="solid"
                        />
                    @endif
                </td>
                <td>
                    @can('manageTeam', $team)
                        <x-forms.button
                            title="Edit Roles for {{ $user->name }}"
                            icon="pencil-square"
                            size="xs"
                            wire:click="edit('{{ $user->id }}')"
                        />
                        <x-forms.button.delete
                            title="Remove {{ $user->name }} from {{ $team->name }}"
                            size="xs"
                            wire:click.prevent="remove('{{ $user->id }}')"
                            wire:confirm="Are you sure you want to remove &quot;{{ $user->name }}&quot; from the team?"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @can('manageTeam', $team)
        <flux:modal.trigger name="add-user">
            <x-forms.button icon="plus-circle">Add User</x-forms.button>
        </flux:modal.trigger>
        <flux:modal name="add-user" wire:close="closeAddUser()" class="md:w-96">
            <livewire:teams.add-team-user :team="$team"/>
        </flux:modal>

        <flux:modal name="edit-user" wire:close="closeEditUser()" class="md:w-96">
            @if($editUser)
                <livewire:teams.update-roles :team="$team" :user="$editUser"/>
            @endif
        </flux:modal>
    @endcan
</div>
