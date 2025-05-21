<div>
    <h1>{{ $this->project->name }}: Add Scope</h1>

    <form wire:submit="save">
        @include('livewire.scopes.fields')
        <x-forms.button.submit-group submitName="Add Scope" />
    </form>
</div>

<x-slot:sidebarPrimary>
    <h3>Instructions</h3>
    <p>
        Generally a scope is a specific page or screen, identified by the page title and URL.
    </p>
    <p>
        Scopes can also be used to group elements being reviewed like overall site template issues or components shared across pages.
    </p>
</x-slot:sidebarPrimary>
