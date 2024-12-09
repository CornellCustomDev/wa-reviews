@props([
    'title',
])
@php
    $attributes = $attributes->merge([
        'variant' => 'cds-secondary',
        'icon' => 'trash',
        'size' => 'sm',
    ])
@endphp
<x-forms.button :$title :$attributes />
