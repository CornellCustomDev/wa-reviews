<x-forms.input label="Title" wire:model="form.title"/>
<x-forms.input
    label="URL"
    description="Optional: Provide a URL if the scope is a specific page."
    wire:model="form.url"
/>
<x-forms.textarea label="Notes" wire:model="form.notes"/>
