<div>
    <form wire:submit="save">
        @include('livewire.users.fields-team')
        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Update Team
            </x-forms.button>
            <x-forms.button wire:click="$dispatch('close-edit-team')" variant="cds-secondary">
                Cancel
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
