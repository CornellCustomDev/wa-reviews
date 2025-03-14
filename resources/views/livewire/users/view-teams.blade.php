<div>
    <table class="table striped bordered">
        <thead>
            <tr>
                <th style="width: 50px;">ID</th>
                <th>Name</th>
                <th style="width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teams as $team)
                <tr wire:key="{{ $team->id }}">
                    <td>
                        {{ $team->id }}
                    </td>
                    <td>
                        {{ $team->name }}
                    </td>
                    <td class="text-nowrap">
                        @can('update', $team)
                            <x-forms.button
                                title="Edit Team {{ $team->id }}"
                                icon="pencil-square"
                                size="xs"
                                wire:click="edit('{{ $team->id }}')"
                            />
                        @endcan
                        @can('delete', $team)
                            <x-forms.button.delete
                                title="Delete Team {{ $team->id }}"
                                size="xs"
                                wire:click.prevent="delete('{{ $team->id }}')"
                                wire:confirm="Are you sure you want to delete the team &quot;{{ $team->name }}&quot;?"
                            />
                        @endcan
                </tr>
            @endforeach
        </tbody>
    </table>

    <x-forms.button icon="plus-circle" wire:click="create()">Add Team</x-forms.button>

    <flux:modal name="edit-team" wire:close="closeEditTeam()" class="max-w-(--breakpoint-xl)">
        @if ($createTeam)
            <livewire:users.create-team />
        @endif
        @if ($editTeam)
            <livewire:users.update-team :team="$editTeam" />
        @endif
    </flux:modal>
</div>
