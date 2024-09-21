@props([
    'title' => null,
    'sidebar' => false,
    'supplementary' => false,
    'footer' => false,
    'breadcrumbs' => [],
])
@include('components.cd.layout.app', [
    'subtitle' => $title,
    'sidebar' => $sidebar,
    'supplementary' => $supplementary,
    'footer' => $footer,
    'breadcrumbs' => $breadcrumbs,
])
