@blaze(fold: true)

@props([
    'name' => null,
])

@php
$classes = Flux::classes()
    ->add('flex gap-2')
    ;

$buttonClasses = Flux::classes()
    ->add('transition-opacity duration-200')
    ->add('[&[disabled]]:opacity-50 [&[disabled]]:pointer-events-none')
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-carousel-controls>
    <ui-carousel-button direction="previous" @if ($name) name="{{ $name }}" @endif class="{{ $buttonClasses }} data-at-start:opacity-50 data-at-start:pointer-events-none">
        <button type="button" aria-label="{{ __('Previous slide') }}" class="rounded-full border border-zinc-200 size-8 flex justify-center items-center dark:border-white/10 bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600/75">
            <flux:icon.chevron-left variant="mini" class="text-zinc-800 dark:text-zinc-200 rtl:-scale-x-100" />
        </button>
    </ui-carousel-button>

    <ui-carousel-button direction="next" @if ($name) name="{{ $name }}" @endif class="{{ $buttonClasses }} data-at-end:opacity-50 data-at-end:pointer-events-none">
        <button type="button" aria-label="{{ __('Next slide') }}" class="rounded-full border border-zinc-200 size-8 flex justify-center items-center dark:border-white/10 bg-white hover:bg-zinc-50 dark:bg-zinc-700 dark:hover:bg-zinc-600/75">
            <flux:icon.chevron-right variant="mini" class="text-zinc-800 dark:text-zinc-200 rtl:-scale-x-100" />
        </button>
    </ui-carousel-button>
</div>
