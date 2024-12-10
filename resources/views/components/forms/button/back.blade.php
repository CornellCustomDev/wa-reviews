@props([
    'href',
])
@php
    $attributes = $attributes->merge([
        'href' => $href,
        'variant' => 'cds',
        'icon' => 'arrow-left',
        'size' => 'sm',
    ]);
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
