@props([
    'href',
])
@php
    $attributes = $attributes->merge([
        'href' => $href,
        'icon' => 'plus-circle',
    ]);
@endphp
<x-forms.button :$attributes>{!! trim($slot) !!}</x-forms.button>
