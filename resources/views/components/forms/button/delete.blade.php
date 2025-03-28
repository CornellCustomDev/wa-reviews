@php
    $attributes = $attributes->merge([
        'icon' => 'trash',
    ])
@endphp
<x-forms.button :$attributes class="secondary">{!! trim($slot) !!}</x-forms.button>
