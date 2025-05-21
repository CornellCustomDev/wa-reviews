<x-forms.input label="Title" wire:model="form.title" description="Name of the page or pages" required/>
<x-forms.input
    label="URL"
    description="Provide a URL if the scope is a specific page."
    wire:model="form.url"
/>
<x-forms.textarea label="Notes" wire:model="form.notes">
    <x-slot name="description">
        Additional information to help identify the testing needs or the context of this scope.
    </x-slot>
</x-forms.textarea>
