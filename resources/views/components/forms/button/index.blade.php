@props([
    // Default all buttons to cds variant
    'variant' => 'cds',
])
<flux:button :$variant :$attributes>{!! trim($slot) !!}</flux:button>
