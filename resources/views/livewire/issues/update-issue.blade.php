<div>
    <form wire:submit="save">
        <x-forms.select
            variant="combobox"
            label="Scope"
            placeholder="Choose scope..."
            wire:model="form.scope_id"
            :options="$this->form->scopeOptions"
        />

        @include('livewire.issues.fields')

        <x-forms.button.submit-group submitName="Update Issue" />
    </form>
</div>
