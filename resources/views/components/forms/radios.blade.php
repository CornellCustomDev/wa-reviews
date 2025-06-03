@props([
    'label',
    'values',
    'badge' => null,
    'description' => null,
    'variant' => 'default',
])

@php
    $badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:radio.group :$label :$description :$variant :$badge :$attributes>
    @foreach ($values as $option)
        <flux:radio :value="$option['value']" :label="$option['label']" :description="$option['description'] ?? null"/>
    @endforeach
</flux:radio.group>
