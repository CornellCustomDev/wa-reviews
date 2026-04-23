<div>
    <form wire:submit="save">
        <x-forms.input label="Name" wire:model="form.name" />

        <x-forms.select label="WCAG 2 criterion" wire:model="form.criterion_id" :options="$this->criterionOptions" />

        <x-forms.select label="Category" wire:model="form.category_id" :options="$this->categoryOptions" />

        <x-forms.textarea label="Notes" wire:model="form.notes" size="lg"/>

        <x-forms.button.submit-group>
            <x-forms.button type="submit">Update Guideline</x-forms.button>
            <x-forms.button x-on:click.prevent="$dispatch('close-edit')" class="secondary">Cancel</x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
