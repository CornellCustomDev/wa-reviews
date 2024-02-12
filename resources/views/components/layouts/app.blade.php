@props([
    'title' => null,
    'sidebar' => true,
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
