@props([
    'showChat' => false,
])
@php
    $attributes = $attributes->merge([
        'variant' => $showChat ? 'cds-secondary' : 'cds',
        'icon' => 'chat-bubble-left-right',
    ]);
@endphp
<x-forms.button :$attributes>{{ $showChat ? 'Hide Chat' : 'Chat' }}</x-forms.button>
