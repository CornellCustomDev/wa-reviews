@props([
    'label',
    'options',
    'badge' => null,
])

@php
$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:select :$label :$badge :$attributes >
    @foreach ($options as $option)
        <flux:select.option :value="$option['value']">{{ $option['option'] }}</flux:select.option>
    @endforeach
</flux:select>
