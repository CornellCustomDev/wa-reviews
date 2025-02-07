<div>
    <h1>{{ $scope->title }}: Add Issue</h1>

    <flux:subheading class="mb-4">
        <a href="{{ $this->siteimproveUrl() }}#/sia-r{{ $rule->id }}/failed"
           target="_blank" title="View Siteimprove report for {{ $rule->name }}">
            Siteimprove Issue Detail
            <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
        </a>
    </flux:subheading>

    <form wire:submit="save">
        <x-forms.input type="text" label="Target" wire:model="form.target" />
        <x-forms.textarea label="Description" wire:model="form.description" />
        <x-forms.checkbox
            label="Generate guideline observations with AI"
            description="Guideline failures will be populated based on the target and description."
            wire:model="form.generateGuidelines"
        />

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
</div>
