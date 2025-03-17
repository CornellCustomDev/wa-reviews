<div>
    <form wire:submit="save">
        <x-forms.input label="Responsible unit at Cornell" wire:model="form.responsible_unit" />
        <x-forms.fieldset legend="Point of Contact">
                <x-forms.input label="Name" wire:model="form.contact_name" />
                <x-forms.input label="NetID" wire:model="form.contact_netid" />
        </x-forms.fieldset>
        <x-forms.input label="Who is the audience?" wire:model="form.audience" />
        <x-forms.textarea label="What is the purpose of the site?" wire:model="form.site_purpose" />
        <x-forms.textarea label="URLs included in review" wire:model="form.urls_included" />
        <x-forms.textarea label="URLs excluded from review" wire:model="form.urls_excluded" />
        <x-forms.textarea label="Review procedure" wire:model="form.review_procedure" />
        <x-forms.textarea label="Summary and Overall Findings" wire:model="form.summary" />
        <x-forms.button.submit-group>
            <x-forms.button type="submit" variant="cds">Update Report</x-forms.button>
            <x-forms.button x-on:click="editReport = false" variant="cds-secondary">Cancel</x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
