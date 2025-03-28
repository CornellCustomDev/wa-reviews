@props([
    'href',
])
@php
    $attributes = $attributes->merge([
        'href' => $href,
        'icon' => 'arrow-left',
    ]);
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
