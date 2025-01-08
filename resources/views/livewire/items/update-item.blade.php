<div>
    <form wire:submit="save">
        @include('livewire.items.fields')

        <div class="mt-8">
            <x-forms.button type="submit" variant="cds">Update Observation</x-forms.button>
        </div>
    </form>
</div>
