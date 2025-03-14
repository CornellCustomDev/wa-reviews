<div>
    <form wire:submit="save">
        @include('livewire.users.fields-team')
        <x-forms.button.submit-group submitName="Create Team" />
    </form>
</div>
