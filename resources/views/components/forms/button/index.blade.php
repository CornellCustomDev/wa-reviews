@props([
    // Default all buttons to "cds" variant, size "sm"
    'variant' => 'cds',
    'size' => 'sm',
])
<flux:button :$variant :$size :$attributes>{!! trim($slot) !!}</flux:button>
