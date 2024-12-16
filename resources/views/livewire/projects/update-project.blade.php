<div>
    <form wire:submit="save">
        @include('livewire.projects.fields')
        <x-forms.button.submit-group submitName="Update Project" />
    </form>
</div>
