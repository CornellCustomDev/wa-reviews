@props([
    'label',
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'description' => null,
    'badge' => null,
])

@php
    $badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:field class="mb-4 max-w-[600px]">
    <flux:label class="!mb-1" :$badge>{{ $label }}</flux:label>

    @if ($description)
        <flux:description class="!mb-1">{{ $description }}</flux:description>
    @endif

    <flux:input :$attributes  />
    <flux:error class="!mt-1" :$name />

</flux:field>
