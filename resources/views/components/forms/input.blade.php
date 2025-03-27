@props([
    'label',
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'description' => null,
    'descriptionTrailing' => null,
    'badge' => null,
])

@php
$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:field>
    <flux:label :$badge>{{ $label }}</flux:label>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    <flux:input :$attributes />

    <flux:error :$name />

    @if ($descriptionTrailing)
        <flux:description class="-mt-0!">{{ $descriptionTrailing }}</flux:description>
    @endif

</flux:field>
