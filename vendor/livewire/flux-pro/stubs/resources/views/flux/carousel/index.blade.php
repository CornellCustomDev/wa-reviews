@blaze(fold: true, unsafe: ['arrows:position', 'autoplay:interval', 'previous', 'next'])

@props([
    'arrows' => true,
    'indicators' => false,
    'snap' => null,
    'scroll' => null,
    'advance' => null,
    'name' => null,
    'disabled' => false,
    'fade' => false,
    'autoplay' => false,
])

@php
$snap = $snap === 'mandatory' ? 'mandatory' : 'proximity';
$scroll = $scroll === 'instant' ? 'instant' : 'smooth';
$advance = $advance === 'page' ? 'page' : 'slide';

$trackClass = $attributes->pluck('track:class', '');
$arrowsClass = $attributes->pluck('arrows:class', '');
$arrowsPosition = $attributes->pluck('arrows:position', 'inside');
$arrowsPosition = in_array($arrowsPosition, ['inside', 'overlap', 'outside']) ? $arrowsPosition : 'inside';
$autoplayInterval = $attributes->pluck('autoplay:interval', 5000);

$classes = Flux::classes()
    ;

$viewportClasses = Flux::classes()
    ->add('relative')
    ;

$controlStateClasses = Flux::classes()
    ->add('data-[disabled]:opacity-50 data-[disabled]:pointer-events-none')
    ->add('[&[disabled]]:opacity-50 [&[disabled]]:pointer-events-none')
    ;

$trackClasses = Flux::classes()
    ->add('flex [:where(&)]:gap-4')
    ->add('overflow-x-auto overflow-y-hidden overscroll-x-contain scroll-smooth [scrollbar-width:none] [&::-webkit-scrollbar]:hidden')
    ->add('snap-x')
    ->add($snap === 'mandatory' ? 'snap-mandatory' : 'snap-proximity')
    ->add($fade ? [
        '[--flux-carousel-fade-size:4rem]',
        '[--flux-carousel-scroll-percentage:0%]', // This is controlled by JavaScript...
        '[--flux-carousel-fade-left:max(calc(100%-var(--flux-carousel-fade-size)),calc(100%-var(--flux-carousel-scroll-percentage)))]',
        '[--flux-carousel-fade-right:max(calc(100%-var(--flux-carousel-fade-size)),var(--flux-carousel-scroll-percentage))]',
        'rtl:[--flux-carousel-fade-left:max(calc(100%-var(--flux-carousel-fade-size)),var(--flux-carousel-scroll-percentage))]',
        'rtl:[--flux-carousel-fade-right:max(calc(100%-var(--flux-carousel-fade-size)),calc(100%-var(--flux-carousel-scroll-percentage)))]',
        'mask-l-from-[var(--flux-carousel-fade-left)]',
        'mask-r-from-[var(--flux-carousel-fade-right)]',
    ] : '')
    ->add($trackClass)
    ;

$previousPositionClasses = match ($arrowsPosition) {
    'overlap' => 'left-0 -translate-x-1/2 rtl:left-auto rtl:right-0 rtl:translate-x-1/2',
    'outside' => 'left-[-44px] rtl:left-auto rtl:right-[-44px]',
    default => 'left-3 rtl:left-auto rtl:right-3',
};

$nextPositionClasses = match ($arrowsPosition) {
    'overlap' => 'right-0 translate-x-1/2 rtl:right-auto rtl:left-0 rtl:-translate-x-1/2',
    'outside' => 'right-[-44px] rtl:right-auto rtl:left-[-44px]',
    default => 'right-3 rtl:right-auto rtl:left-3',
};

$previousClasses = Flux::classes()
    ->add($arrowsClass)
    ->add('absolute top-1/2 -translate-y-1/2 transition-opacity duration-200 in-data-[at-start]:opacity-0 in-data-[at-start]:pointer-events-none not-in-data-[ready]:hidden')
    ->add($controlStateClasses)
    ->add($previousPositionClasses)
    ;

$nextClasses = Flux::classes()
    ->add($arrowsClass)
    ->add('absolute top-1/2 -translate-y-1/2 transition-opacity duration-200 in-data-[at-end]:opacity-0 in-data-[at-end]:pointer-events-none not-in-data-[ready]:hidden')
    ->add($controlStateClasses)
    ->add($nextPositionClasses)
    ;

$indicatorClasses = Flux::classes()
    ->add('mt-6 flex items-center justify-center transition-opacity duration-200')
    ->add($controlStateClasses)
    ;
@endphp

<ui-carousel
    {{ $attributes->class($classes) }}
    data-flux-carousel
    @if ($name) name="{{ $name }}" @endif
    @if ($disabled) disabled @endif
    @if ($autoplay) autoplay autoplay-interval="{{ $autoplayInterval }}" @endif
    @if (! $attributes->has('aria-label') && ! $attributes->has('aria-labelledby')) aria-label="{{ __('Items') }}" @endif
    snap="{{ $snap }}"
    scroll="{{ $scroll }}"
    advance="{{ $advance }}"
>
    <div class="{{ $viewportClasses }}" data-flux-carousel-viewport>
        <div class="{{ $trackClasses }}" data-flux-carousel-track>
            {{ $slot }}
        </div>

        <?php if ($arrows): ?>
            <ui-carousel-button direction="previous" class="{{ $previousClasses }}">
                <?php if (isset($previous) && $previous->isNotEmpty()): ?>
                    {{ $previous }}
                <?php else: ?>
                    <button type="button" aria-label="{{ __('Previous slide') }}" class="rounded-full shadow-xs ring ring-black/5 bg-white size-8 flex justify-center items-center dark:bg-zinc-800 dark:ring-white/10 dark:hover:ring-white/20 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <flux:icon.chevron-left variant="mini" class="text-zinc-700 dark:text-zinc-200 rtl:-scale-x-100" />
                    </button>
                <?php endif; ?>
            </ui-carousel-button>

            <ui-carousel-button direction="next" class="{{ $nextClasses }}">
                <?php if (isset($next) && $next->isNotEmpty()): ?>
                    {{ $next }}
                <?php else: ?>
                    <button type="button" aria-label="{{ __('Next slide') }}" class="rounded-full shadow-xs ring ring-black/5 bg-white size-8 flex justify-center items-center dark:bg-zinc-800 dark:ring-white/10 dark:hover:ring-white/20 hover:bg-zinc-50 dark:hover:bg-zinc-700">
                        <flux:icon.chevron-right variant="mini" class="text-zinc-700 dark:text-zinc-200 rtl:-scale-x-100" />
                    </button>
                <?php endif; ?>
            </ui-carousel-button>
        <?php endif; ?>
    </div>

    <?php if ($indicators): ?>
        <ui-carousel-indicators class="{{ $indicatorClasses }}" role="group" aria-label="{{ __('Choose slide to display') }}">
            <template>
                <button type="button" class="group p-1 flex items-center justify-center disabled:pointer-events-none">
                    <span class="size-2 rounded-full bg-zinc-300 group-hover:bg-zinc-400 group-data-selected:bg-zinc-800 dark:bg-white/30  dark:group-hover:bg-white/50 dark:group-data-selected:bg-white transition-colors"></span>
                </button>
            </template>
        </ui-carousel-indicators>
    <?php endif; ?>
</ui-carousel>
