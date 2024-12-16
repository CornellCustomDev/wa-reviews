@props([
    'label',
    'options'
])

@php
$classes = Flux::classes()
    ->add('!mb-4')
    ;
@endphp

<flux:select :$label {{ $attributes->class($classes) }}>
    @foreach ($options as $option)
        <flux:option :value="$option['value']">{{ $option['option'] }}</flux:option>
    @endforeach
</flux:select>
