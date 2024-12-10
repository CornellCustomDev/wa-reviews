<div>
    <h1>Create Project</h1>

    <form wire:submit="save">
        <x-forms.input label="Project Name" wire:model="form.name" />
        <x-forms.input
            label="Site URL" wire:model="form.site_url"
            description="This URL should be identical to the URL used in Siteimprove."
        />
        <x-forms.input label="Description" wire:model="form.description" />
        <x-forms.input label="Siteimprove Report URL" wire:model="form.siteimprove_url" />
        <x-forms.input
            label="Siteimprove ID" wire:model="form.siteimprove_id"
            description="This field is optional, as it will be added automatically when the project is created."
        />

        <x-forms.button.submit-group submitName="Create Project" />
    </form>
</div>
