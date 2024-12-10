@php
    $attributes = $attributes->merge([
        'variant' => 'cds-secondary',
        'icon' => 'trash',
    ])
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
