<div>
    <h1>{{ $this->project->name }}: Add Scope</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Title" wire:model="form.title" />
        <x-cd.form.text label="URL" wire:model="form.url" />
        <x-forms.textarea label="Notes" wire:model="form.notes" />

        <x-forms.button type="submit" variant="cds">Add Scope</x-forms.button>
        <x-forms.button :href="route('project.show', $this->project)" variant="cds-secondary">Cancel</x-forms.button>
    </form>
</div>
