@props([
    'href',
    'title',
])
@php
    $attributes = $attributes->merge([
        'variant' => 'cds',
        'icon' => 'eye',
        'size' => 'sm',
    ]);
@endphp
<x-forms.button :$href :$title :$attributes />
