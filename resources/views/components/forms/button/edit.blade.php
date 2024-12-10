@props([
    'href',
])
@php
    $attributes = $attributes->merge([
        'href' => $href,
        'variant' => 'cds',
        'icon' => 'pencil-square',
    ]);
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
