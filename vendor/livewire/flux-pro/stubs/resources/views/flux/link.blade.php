@props([
    'variant' => null,
    'external' => null,
])

@php
$classes = Flux::classes()
    ->add('inline text-inherit font-medium')
    ->add('underline-offset-[6px] decoration-zinc-800/20 hover:decoration-current')
    ->add(match ($variant) {
        'ghost' => 'no-underline hover:underline text-zinc-800 dark:text-white',
        'subtle' => 'no-underline text-zinc-500 dark:text-white/70 hover:text-zinc-800 dark:hover:text-white',
        default => 'underline text-zinc-800 dark:text-white',
    })
    ->add('dark:decoration-white/20')
    ;
@endphp
{{-- NOTE: It's important that this file has NO newline at the end of the file. --}}
<a {{ $attributes->class($classes) }} data-flux-link <?php if ($external) : ?>target="_blank"<?php endif; ?>>{{ $slot }}</a>