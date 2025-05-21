@if($this->teams->count() > 1)
    <x-forms.select
        label="Team"
        placeholder="Select a team"
        :options="$this->teams"
        wire:model="form.team_id"
        required
    />
@endif
<x-forms.input label="Project Name" wire:model="form.name" required />
<x-forms.input
    label="Site URL" wire:model="form.site_url"
    description="This URL should be identical to the URL used in Siteimprove."
    required
/>
<x-forms.textarea label="Description" wire:model="form.description" size="sm"/>

<h3>Site Improve</h3>

<x-forms.input label="Siteimprove Report URL" wire:model="form.siteimprove_url">
    <x-slot name="description">
        If there is a Siteimprove review, include the Accessibility report link.
    </x-slot>
</x-forms.input>
<x-forms.input
    label="Siteimprove ID" wire:model="form.siteimprove_id"
    placeholder="The Siteimprove ID will be added automatically when the project is created."
    disabled
/>

<h3>Reporting</h3>

<x-forms.input label="Responsible unit at Cornell" wire:model="form.responsible_unit">
    <x-slot:description>
        Which Cornell unit is responsible for maintaining this site?
    </x-slot:description>
</x-forms.input>

<x-forms.fieldset legend="Point of Contact">
    <x-forms.input label="Name" wire:model="form.contact_name" />
    <x-forms.input label="NetID" wire:model="form.contact_netid" />
</x-forms.fieldset>

<x-forms.input
    label="Who is the audience?"
    wire:model="form.audience"
    description="Example: Students, Staff, Faculty, Visitors, Researchers etc..."
/>
<x-forms.textarea
    label="What is the purpose of the site?"
    wire:model="form.site_purpose"
    description="Describe what the site is used for and what kinds of information exist on it."
/>
