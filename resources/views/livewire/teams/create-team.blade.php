<div>
    <form wire:submit="save">
        <h3>Create Team</h3>

        @include('livewire.teams.fields-team')
        <x-forms.button.submit-group>
            <x-forms.button type="submit">
                Create Team
            </x-forms.button>
            <x-forms.button wire:click="$dispatch('close-edit-team')" class="secondary">
                Cancel
            </x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
