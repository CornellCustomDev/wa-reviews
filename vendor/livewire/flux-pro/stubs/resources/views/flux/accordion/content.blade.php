@blaze(fold: true)

@aware([ 'transition', 'expanded' ])

@props([
    'transition' => false,
    'expanded' => false,
])

@php
$classes = Flux::classes()
    ->add('pt-2 text-sm text-zinc-500 dark:text-zinc-300')
    ;
@endphp

<div
    @if ($transition) x-show="open" x-collapse.min.0px @endif
    @if (! $expanded) hidden="until-found" @endif
    data-flux-accordion-content
>
    <div {{ $attributes->class($classes) }}>
        {{ $slot }}
    </div>
</div>
