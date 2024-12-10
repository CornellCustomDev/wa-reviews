@props([
    'variant' => 'cds',
])
@php
    $attributes = $attributes->merge([
        'variant' => $variant,
    ])
@endphp
<flux:button :$attributes>{!! trim($slot) !!}</flux:button>
