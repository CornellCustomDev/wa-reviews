<div>
    <form wire:submit="save">
        <x-forms.textarea
            label="What is the purpose of the site?"
            wire:model="form.site_purpose"
            size="sm"
            description="Describe what the site is used for and what kinds of information exist on it."
        />
        <x-forms.textarea
            label="URLs included in review"
            wire:model="form.urls_included"
            size="sm"
            description="Briefly describe what the scope of the review was - what pages were included as part of the assessment."
        />
        <x-forms.textarea
            label="URLs excluded from review"
            wire:model="form.urls_excluded"
            size="sm"
            description="Briefly describe any pages that were reachable from the site or application that were not part of the scope of this review."
        />
        <x-forms.textarea
            label="Testing notes and procedure"
            wire:model="form.review_procedure"
            description="Mention the browser you tested in, which operating system, and your screen reader/browser combination, as well as any other relevant information related to your review process."
        />
        <x-forms.textarea
            label="Overview of findings"
            wire:model="form.summary"
            size="lg"
            description="Summarize your findings as a whole in a few sentences."
        />

        <x-forms.button.submit-group>
            <x-forms.button type="submit">Update Report</x-forms.button>
            <x-forms.button x-on:click="editReport = false" class="secondary">Cancel</x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
