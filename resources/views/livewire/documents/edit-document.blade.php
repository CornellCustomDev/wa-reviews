<div>
    <form wire:submit="save">
        <x-forms.input label="Title" wire:model="form.title" />
        <x-forms.textarea label="Content" wire:model="form.content" size="lg"/>
        <x-forms.button.submit-group>
            <x-forms.button type="submit">Update Document</x-forms.button>
            <x-forms.button x-on:click.prevent="$dispatch('close-edit')" class="secondary">Cancel</x-forms.button>
        </x-forms.button.submit-group>
    </form>
</div>
