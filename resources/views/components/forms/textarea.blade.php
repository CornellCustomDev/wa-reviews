@props([
    'label',
    'id' => $attributes->whereStartsWith('wire:model')->first(),
])
<x-cd.form.form-item field="{{ $id }}">
    <x-slot name="field_title">{{ $label }}</x-slot>
    <textarea {{ $attributes->merge(['id' => $id]) }}>{{ $slot }}</textarea>
</x-cd.form.form-item>
