<div>
    <form wire:submit="save">
        <flux:heading>Update User</flux:heading>
        <hr>
        <x-forms.input wire:model="form.name" label="Name" />
        <x-forms.input wire:model="form.email" label="Email" disabled />
        <flux:checkbox.group label="Teams" wire:model="form.teams">
            @foreach($this->getTeams() as $team)
                <x-forms.checkbox
                    label="{{ $team->name }}"
                    value="{{ $team->id }}"
                />
            @endforeach
        </flux:checkbox.group>
        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Update User
            </x-forms.button>
            <x-forms.button wire:click="$dispatch('close-edit-user')" variant="cds-secondary">
                Cancel
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
