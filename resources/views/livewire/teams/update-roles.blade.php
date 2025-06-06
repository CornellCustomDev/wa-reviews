<div>
    <form wire:submit="save">
        <h3>Update User</h3>

        <x-forms.input wire:model="form.name" label="Name" disabled />
        <flux:checkbox.group label="Roles" wire:model="form.roles">
            @foreach($this->getRoles() as $role)
                <x-forms.checkbox
                    label="{{ $role->display_name }}"
                    value="{{ $role->id }}"
                />
            @endforeach
        </flux:checkbox.group>
        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Update User
            </x-forms.button>
            <x-forms.button wire:click="$dispatch('close-edit-user')" class="secondary">
                Cancel
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
