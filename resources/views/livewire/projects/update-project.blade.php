<div>
    <h1>Edit Project</h1>

    <form wire:submit="save">
        @include('livewire.projects.fields')
        <x-forms.button.submit-group submitName="Update Project" />
    </form>
</div>
