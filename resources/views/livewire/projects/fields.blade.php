
<x-forms.input label="Project Name" wire:model="form.name" />
<x-forms.input
    label="Site URL" wire:model="form.site_url"
    description="This URL should be identical to the URL used in Siteimprove."
/>
<x-forms.textarea label="Description" wire:model="form.description" />
<x-forms.input label="Siteimprove Report URL" wire:model="form.siteimprove_url" />
<x-forms.input
    label="Siteimprove ID" wire:model="form.siteimprove_id"
    placeholder="The Siteimprove ID will be added automatically when the project is created."
    disabled
/>
