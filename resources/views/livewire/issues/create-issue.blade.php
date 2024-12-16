<div>
    <h1>{{ $this->scope->title }}: Add Issue</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target" />
        <x-forms.textarea label="Description" wire:model="form.description" />

        <x-forms.button type="submit">Add Issue</x-forms.button>
        <x-forms.button :href="route('scope.show', $this->scope)" variant="cds-secondary">Cancel</x-forms.button>
    </form>
</div>
