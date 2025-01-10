<div>
    <form wire:submit="save">
        @include('livewire.scopes.fields')
        <x-forms.button.submit-group submitName="Update Scope" />
    </form>
</div>
