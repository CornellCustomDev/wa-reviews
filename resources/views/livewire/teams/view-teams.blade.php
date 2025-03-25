<div>
    <table class="table striped bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th class="w-24">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teams as $team)
                <tr wire:key="{{ $team->id }}">
                    <td>
                        {{ $team->name }}
                    </td>
                    <td class="text-nowrap">
                        <x-forms.button.view
                            title="View {{ $team->name }}"
                            size="xs"
                            :href="route('teams.show', $team)"
                        />
                        @can('update', $team)
                            <x-forms.button
                                title="Edit Team {{ $team->name }}"
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

    @can('create', App\Models\Team::class)
        <x-forms.button icon="plus-circle" wire:click="create()">Add Team</x-forms.button>
    @endcan

    <flux:modal name="edit-team" wire:close="closeEditTeam()" class="max-w-(--breakpoint-xl)">
        @if ($createTeam)
            <livewire:teams.create-team />
        @endif
        @if ($editTeam)
            <livewire:teams.update-team :team="$editTeam" />
        @endif
    </flux:modal>
</div>
