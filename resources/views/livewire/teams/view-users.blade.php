<div>
    <table class="table striped bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Teams</th>
                <th class="w-24">Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr wire:key="{{ $user->id }}">
                <td>
                    {{ $user->name }}
                    @if($user->hasRole(\App\Enums\Roles::SiteAdmin))
                        (Site Admin)
                    @endif
                </td>
                <td>
                    {{ $user->email }}
                </td>
                <td class="text-nowrap">
                    <ul>
                        @foreach($user->teams as $team)
                            <li>
                                {{ $team->name }}
                                @if($team->isTeamAdmin($user))
                                    (Team Admin)
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </td>
                <td>
                    @can('update', $user)
                        <x-forms.button
                            title="Edit User {{ $user->id }}"
                            icon="pencil-square"
                            size="xs"
                            class="float-right"
                            wire:click="edit('{{ $user->id }}')"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
    </table>

    <flux:modal name="edit-user" wire:close="closeEditUser()" class="max-w-(--breakpoint-xl)">
        @if ($editUser)
            <livewire:teams.update-user :user="$editUser" />
        @endif
    </flux:modal>
</div>
