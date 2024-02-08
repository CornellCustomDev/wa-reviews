@props([
    'title' => null,
    'sidebar' => true
])
@include('components.cd.layout.app', ['subtitle' => $title, 'sidebar' => $sidebar])
