@props([
    'label',
    'options',
    'description' => null,
    'descriptionTrailing' => null,
    'badge' => null,
])

@php
$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:select :$label :$badge :$description :$descriptionTrailing :$attributes >
    @foreach ($options as $option)
        <flux:select.option :value="$option['value']">{{ $option['option'] }}</flux:select.option>
    @endforeach
</flux:select>
