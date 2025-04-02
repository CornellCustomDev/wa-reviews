@props([
    'size' => 'sm',
])
@php
// add a class of primary unless the class is already set
$attributes = $attributes->merge([
    'class' => 'primary',
]);
@endphp
<flux:button :$size :$attributes>{!! trim($slot) !!}</flux:button>
