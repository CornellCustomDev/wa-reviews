<div>
    <form wire:submit="save">
        <h3>Update Observation</h3>

        @include('livewire.items.fields')

        <div class="mt-8">
            <x-forms.button type="submit">Update Observation</x-forms.button>
        </div>
    </form>
</div>
