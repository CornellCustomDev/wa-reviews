@props([
    'label',
    'values',
    'badge' => null,
])

@php
    $badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:radio.group :$label :$attributes size="sm" :$badge>
    @foreach ($values as $option)
        <flux:radio :value="$option['value']" :label="$option['label']" :description="$option['description'] ?? ''"/>
    @endforeach
</flux:radio.group>
