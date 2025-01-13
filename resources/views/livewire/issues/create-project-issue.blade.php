<div>
    <h1>{{ $this->project->name }}: Add Issue</h1>

    <form wire:submit="save">
        <x-forms.select
            variant="combobox"
            label="Scope"
            placeholder="Choose scope..."
            wire:model="form.scope_id"
            :options="$this->form->scopeOptions"
            description="Specify the page this is on."
        />
        <x-forms.input
            label="Target"
            wire:model="form.target"
            required
        />
        <x-forms.textarea label="Description" wire:model="form.description" />
        <x-forms.checkbox
            label="Generate guideline observations with AI"
            description="Guideline failures will be populated based on the target and description."
            wire:model="form.generateGuidelines"
        />

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
</div>
