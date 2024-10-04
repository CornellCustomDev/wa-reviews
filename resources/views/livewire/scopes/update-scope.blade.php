<div>
    <h1>Edit Scope</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Title" wire:model="form.title" />
        <x-cd.form.text label="URL" wire:model="form.url" />
        <x-cd.form.text label="Siteimprove Report URL" wire:model="form.siteimprove_url" />
        <x-forms.textarea label="Notes" wire:model="form.notes" />

        <input type="submit" value="Update Scope" />
        <a href="{{ route('scope.show', $form->scope) }}" >
            <input type="button" value="Cancel" />
        </a>
    </form>
</div>
