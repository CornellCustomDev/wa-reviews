@php
    $attributes = $attributes->merge([
        'variant' => 'cds',
    ])
@endphp
<flux:button :$attributes>{!! trim($slot) !!}</flux:button>
