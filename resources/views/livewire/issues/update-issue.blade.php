<div>
    <form wire:submit="save">
        <x-forms.select
            variant="combobox"
            label="Scope"
            placeholder="Choose scope..."
            wire:model="form.scope_id"
            :options="$this->form->scopeOptions"
        />
        <x-forms.input type="text" label="Target" wire:model="form.target" required />
        <x-forms.textarea label="Description" wire:model="form.description" />

        <x-forms.button.submit-group submitName="Update Issue" />
    </form>
</div>
