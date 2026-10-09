@blaze(fold: true)

@props([
    //
])

@php
$classes = Flux::classes()
    ->add('shrink-0 snap-start')
    ->add('[[snap="mandatory"]_&]:snap-always')
    ;
@endphp

<ui-carousel-slide {{ $attributes->class($classes) }} data-flux-carousel-slide>
    {{ $slot }}
</ui-carousel-slide>
