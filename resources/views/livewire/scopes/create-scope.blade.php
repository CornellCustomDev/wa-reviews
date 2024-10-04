<div>
    <h1>{{ $this->project->name }}: Add Scope</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Title" wire:model="form.title" />
        <x-cd.form.text label="URL" wire:model="form.url" />
        <x-cd.form.text label="Siteimprove Report URL" wire:model="form.siteimprove_url" />
        <x-forms.textarea label="Notes" wire:model="form.notes" />

        <input type="submit" value="Add Scope">
        <a href="{{ route('project.show', $this->project) }}" >
            <input type="button" value="Cancel">
        </a>
    </form>
</div>
