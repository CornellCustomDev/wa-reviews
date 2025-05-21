<div>
    <h1>{{ $this->scope->title }}: Add Issue</h1>

    @if($this->scope->url)
        <x-forms.field-display label="URL" variation="inline">
            <a href="{{ $this->scope->url }}" target="_blank">{{ $this->scope->url }}</a>
        </x-forms.field-display>
    @endif

    <form wire:submit="save">
        @include('livewire.issues.fields', ['form' => $form])

        <x-forms.checkbox
            label="Generate guideline observations with AI"
            wire:model="form.generateGuidelines"
        >
            <x-slot name="description">
                If selected, guideline failures and warnings will be populated by AI based on the target
                and description, and potentially the page source.
            </x-slot>
        </x-forms.checkbox>

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
</div>

<x-slot:sidebarPrimary>
    <h3>Instructions</h3>
    <p>
        An Issue identifies an accessibility problem. Additional details, like assessments for
        particular guidelines, are set via the issue details page after the issue is created.
    </p>
    <x-forms.field-display label="Target" variation="inline">
        Describe <strong>what</strong> exactly is causing the issue.
    </x-forms.field-display>
    <x-forms.field-display label="Description" variation="inline">
        Describe <strong>why</strong> there is an issue.
    </x-forms.field-display>


    <x-forms.field-display label="Generate guideline observations with AI">
        When selected, this option will populate any applicable guidelines that AI identifies. These
        can be approved or rejected. You can also edit these or add your own observations.
    </x-forms.field-display>
</x-slot:sidebarPrimary>
