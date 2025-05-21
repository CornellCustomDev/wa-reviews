@props([
    'form',
])
<x-forms.input type="text" label="Target" wire:model="form.target" required>
    <x-slot name="description">
        Identify the element causing the issue. You can describe the appearance, use a CSS selector,
        or whatever else communicates exactly what on the page is causing a failure.
    </x-slot>
</x-forms.input>
<x-forms.textarea label="Description" wire:model="form.description">
    <x-slot name="description">
        What was the observed functionality or behavior that is causing the issue?
    </x-slot>
</x-forms.textarea>
