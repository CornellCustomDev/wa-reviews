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
        {{-- Null label/description keep this component's props from leaking into each option (livewire/flux#2300) --}}
        <flux:select.option :value="$option['value']" :label="null" :description="null">{!! $option['option'] !!}</flux:select.option>
    @endforeach
</flux:select>
