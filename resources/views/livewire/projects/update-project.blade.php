<div>
    <h1>Edit Project</h1>

    <form wire:submit="save">
        <x-forms.input label="Project Name" wire:model="form.name" />
        <x-forms.input label="Site URL" wire:model="form.site_url" />
        <x-forms.input label="Description" wire:model="form.description" />
        <x-forms.input label="Siteimprove Report URL" wire:model="form.siteimprove_url" />
        <x-forms.input label="Siteimprove ID" wire:model="form.siteimprove_id" />

        <x-forms.button.submit-group submitName="Update Project" />
    </form>
</div>
