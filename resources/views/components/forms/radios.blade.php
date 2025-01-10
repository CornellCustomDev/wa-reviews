@props([
    'label',
    'values',
    'badge' => null,
])

@php
    $badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:radio.group :$label :$attributes size="sm" variant="horizontal" :$badge>
    @foreach ($values as $option)
        <flux:radio :value="$option['value']" :label="$option['label']"/>
    @endforeach
</flux:radio.group>
