@props([
    'size' => 'sm',
])
<flux:button :$size :$attributes>{!! trim($slot) !!}</flux:button>
