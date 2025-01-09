<x-forms.input label="Title" wire:model="form.title" required/>
<x-forms.input
    label="URL"
    description="Provide a URL if the scope is a specific page."
    wire:model="form.url"
/>
<x-forms.textarea label="Notes" wire:model="form.notes"/>
