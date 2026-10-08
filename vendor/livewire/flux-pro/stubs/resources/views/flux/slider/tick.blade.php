@blaze(fold: true, safe: ['value'], unsafe: ['variant'])

@props(['value', 'variant' => 'line'])

@php
    $classes = Flux::classes()
        ->add('relative w-px h-full min-h-4 min-w-4 flex flex-col justify-center items-center text-xs font-medium whitespace-nowrap -translate-x-1/2 rtl:translate-x-1/2')
        ->add(match ($variant) {
            'dot' => 'text-black/25 dark:text-white/25 in-data-[flux-slider-tick-position=inside]:data-active:text-white/50',
            default => 'text-zinc-400 data-active:text-zinc-500 dark:text-white/70 dark:data-active:text-white',
        })
    ;

    $tickLineClasses = Flux::classes()
        ->add('h-1 w-px bg-black/25 dark:bg-white/25')
    ;

    $tickDotClasses = Flux::classes()
        ->add('size-1 shrink-0 rounded-full bg-current')
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-slider-tick data-value="{{ $value }}" size="sm" variant="subtle">
    <?php if ($variant === 'dot'): ?>
        <span data-flux-slider-tick-dot class="{{ $tickDotClasses }}"></span>
    <?php elseif ($slot->isNotEmpty()): ?>
        {{ $slot }}
    <?php else: ?>
        <span data-flux-slider-tick-line class="{{ $tickLineClasses }}"></span>
    <?php endif; ?>
</div>
