@props([
    'showChat' => false,
])
@php
    $attributes = $attributes->merge([
        'icon' => 'chat-bubble-left-right',
    ]);
@endphp
<x-forms.button :$attributes @class(['secondary' => $showChat])>{{ $showChat ? 'Hide Chat' : 'Chat' }}</x-forms.button>
