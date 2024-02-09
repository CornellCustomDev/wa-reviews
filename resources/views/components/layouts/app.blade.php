@props([
    'title' => null,
    'sidebar' => true,
    'supplementary' => false,
    'footer' => false,
])
@include('components.cd.layout.app', [
    'subtitle' => $title,
    'sidebar' => $sidebar,
    'supplementary' => $supplementary,
    'footer' => $footer,
])
