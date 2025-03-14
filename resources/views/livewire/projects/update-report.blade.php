<div>
    <form wire:submit="save">
        <x-forms.input label="Responsibile unit at Cornell" wire:model="form.responsible_unit" />
        <flux:fieldset legend="Point of Contact" description="Who is the primary contact for this project?">
            <x-forms.input label="Name" wire:model="form.poc_name" />
            <x-forms.input label="NetID" wire:model="form.poc_netid" />
        </flux:fieldset>
        <x-forms.input label="Who is the audience?" wire:model="form.audience" />
        <x-forms.textarea label="What is the purpose of the site?" wire:model="form.site_purpose" />
        <x-forms.textarea label="URLs included in review" wire:model="form.urls_included" />
        <x-forms.textarea label="URLs excluded from review" wire:model="form.urls_excluded" />
        <x-forms.input type="url" label="WCAG 2 checklist link" wire:model="form.wcag_checklist_link" /> {{-- TODO: Supporting Documentation can be multiple URLs. --}}
        <x-forms.textarea label="Testing notes and procedure" wire:model="form.testing_notes" />
        <x-forms.textarea label="Summary and Overall Findings" wire:model="form.summary" />
        <x-forms.button.submit-group submitName="Update Report" />
    </form>
</div>
