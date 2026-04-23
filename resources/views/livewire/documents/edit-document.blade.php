<div x-data="{ showEdit: false }"
     x-on:show-edit="showEdit = true"
     x-on:close-edit="showEdit = false"
>
    <x-forms.button icon="pencil-square" class="float-right" title="Edit"
        x-show="!showEdit" x-on:click="$dispatch('show-edit')"
    />
    <x-forms.button icon="x-mark" x-cloak class="float-right secondary" title="Cancel editing"
        x-show="showEdit" x-on:click="$dispatch('close-edit')"
    />
    <div x-show="showEdit" x-cloak>
        <form wire:submit="save">
            <x-forms.input label="Title" wire:model="form.title" />
            <x-forms.textarea label="Content" wire:model="form.content" size="lg"/>
            <x-forms.button.submit-group>
                <x-forms.button type="submit">Update Document</x-forms.button>
                <x-forms.button x-on:click.prevent="$dispatch('close-edit')" class="secondary">Cancel</x-forms.button>
            </x-forms.button.submit-group>
        </form>
    </div>
</div>
