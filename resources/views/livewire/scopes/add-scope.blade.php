<div>
    <flux:modal name="add-scope">
        <form class="mb-0!" wire:submit.prevent="save">
            <h3>Add a Scope</h3>

            @include('livewire.scopes.fields')

            <x-forms.button type="submit">Add Scope</x-forms.button>
        </form>
    </flux:modal>
</div>
