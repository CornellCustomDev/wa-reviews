<div>
    <h1>{{ $this->scope->title }}: Add Issue</h1>

    <form wire:submit="save">
        <x-cd.form.text label="Target" wire:model="form.target" />
        <x-forms.textarea label="Description" wire:model="form.description" />
        <x-forms.checkbox
            label="Generate guideline observations with AI"
            description="Guideline failures will be populated based on the target and description."
            wire:model="form.generateGuidelines"
        />

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
</div>
