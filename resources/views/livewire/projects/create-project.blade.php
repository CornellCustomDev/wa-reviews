<div>
    <h1>Create Project</h1>

    <form wire:submit="save">
        @include('livewire.projects.fields')
        <x-forms.button.submit-group submitName="Create Project" />
    </form>
</div>
