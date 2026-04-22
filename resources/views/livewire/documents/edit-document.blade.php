<div x-data="{ showEdit: false }" @close-edit.window="showEdit = false">
    <x-forms.button icon="pencil-square" class="float-right" x-show="!showEdit" x-on:click="showEdit = true" title="Edit" />
    <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="showEdit" x-on:click="showEdit = false" title="Cancel editing" />
    <div x-show="showEdit" x-cloak>
        <form wire:submit="save">
            <x-forms.input label="Title" wire:model="form.title" />
            <x-forms.textarea label="Content" wire:model="form.content" size="lg"/>
            <x-forms.button.submit-group>
                <x-forms.button type="submit">Update Document</x-forms.button>
                <x-forms.button x-on:click="showEdit = false" class="secondary">Cancel</x-forms.button>
            </x-forms.button.submit-group>
        </form>
    </div>
</div>
