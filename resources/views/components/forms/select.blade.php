@props([
    'label',
    'options',
    'badge' => null,
])

@php
$classes = Flux::classes()
    ->add('max-w-[600px]')
    ->add('!mb-4')
    ;

$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp

<flux:select :$label {{ $attributes->class($classes) }} :$badge >
    @foreach ($options as $option)
        <flux:option :value="$option['value']">{{ $option['option'] }}</flux:option>
    @endforeach
</flux:select>
