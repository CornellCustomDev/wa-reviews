<div>
    <h1>{{ $this->project->name }}: Add Scope</h1>

    <form wire:submit="save">
        @include('livewire.scopes.fields')
        <x-forms.button.submit-group submitName="Add Scope" />
    </form>
</div>
