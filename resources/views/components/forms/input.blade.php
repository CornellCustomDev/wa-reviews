@props([
    'label',
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'descriptionTrailing' => null,
    'description' => null,
    'badge' => null,
])

@php
$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;

$classes = Flux::classes()
    ->add('border-cds-gray-400 rounded-none');
@endphp

<flux:field class="mb-4 max-w-[600px]">
    <flux:label class="mb-1!" :$badge>{{ $label }}</flux:label>

    @if ($description)
        <flux:description class="mb-1!">{{ $description }}</flux:description>
    @endif

    <flux:input {{ $attributes->class($classes) }} />

    <flux:error class="mt-1!" :$name />

    @if ($descriptionTrailing)
        <flux:description>{{ $descriptionTrailing }}</flux:description>
    @endif

</flux:field>
