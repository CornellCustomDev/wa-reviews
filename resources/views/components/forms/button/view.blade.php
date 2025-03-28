@props([
    'href',
])
@php
    $attributes = $attributes->merge([
        'href' => $href,
        'icon' => 'eye',
    ]);
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
