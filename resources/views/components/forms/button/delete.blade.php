@php
    $attributes = $attributes->merge([
        'variant' => 'cds-secondary',
        'icon' => 'trash',
        'size' => 'sm',
    ])
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
