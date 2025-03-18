<div>
    <form wire:submit="save">
        <x-forms.input label="Responsible unit at Cornell" wire:model="form.responsible_unit" />
        <x-forms.fieldset legend="Point of Contact">
                <x-forms.input label="Name" wire:model="form.contact_name" />
                <x-forms.input label="NetID" wire:model="form.contact_netid" />
        </x-forms.fieldset>
        <x-forms.input label="Who is the audience?" wire:model="form.audience" description="Example: Students, Staff, Faculty, Visitors, Researchers etc..." />
        <x-forms.textarea label="What is the purpose of the site?" wire:model="form.site_purpose" description="Describe what the site is used for and what kinds of information exist on it."/>
        <x-forms.textarea label="URLs included in review" wire:model="form.urls_included" description="Briefly describe what the scope of the review was - what pages were included as part of the assessment." />
        <x-forms.textarea label="URLs excluded from review" wire:model="form.urls_excluded" description="Briefly describe any pages that were reachable from the site or application that were not part of the scope of this review." />
        <x-forms.textarea label="Review procedure" wire:model="form.review_procedure" description="Mention the browser you tested in, which operating system, and your screen reader/browser combination, as well as any other relevant information related to your review process."/>
        <x-forms.textarea label="Summary and Overall Findings" wire:model="form.summary" description="Summarize your findings as a whole in a few sentences."/>
        <x-forms.button.submit-group>
            <x-forms.button type="submit" variant="cds">Update Report</x-forms.button>
            <x-forms.button x-on:click="editReport = false" variant="cds-secondary">Cancel</x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
