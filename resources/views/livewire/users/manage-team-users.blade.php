<div>
    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Roles</th>
            <th style="width: 100px;">Actions</th>
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
                <td>
                    @can('update', $user)
                        <x-forms.button
                            title="Edit Roles for {{ $user->name }}"
                            icon="pencil-square"
                            size="xs"
                            class="float-right"
                            wire:click="edit('{{ $user->id }}')"
                        />
                    @endcan
                    <ul>
                        @foreach($user->getRoleIdsForTeam($team) as $roleId)
                            <li>{{ $roles->find($roleId)->display_name }}</li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    @can('manageTeam', $team)
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
        <flux:modal name="add-user" class="md:w-96 max-w-(--breakpoint-xl)">
            <livewire:users.add-team-user :team="$team" />
        </flux:modal>

        <flux:modal name="edit-user" wire:close="closeEditUser()" class="md:w-96 max-w-(--breakpoint-xl)">
            @if($editUser)
                <livewire:users.update-roles :team="$team" :user="$editUser" />
            @endif
        </flux:modal>
    @endcan
</div>
