@blaze(fold: true)

@props([
    'label',
])

@php
$classes = Flux::classes([
    '-mx-[.3125rem] px-[.3125rem]',
    '[&+&>[data-flux-option-group-separator-top]]:hidden [&:first-of-type>[data-flux-option-group-separator-top]]:hidden [&:last-child>[data-flux-option-group-separator-bottom]]:hidden',
    '[&:not(:has(ui-option:not([data-hidden])))]:hidden',
]);

$labelClasses = Flux::classes([
    'p-2 pb-1 w-full',
    'text-start text-xs font-medium',
    'text-zinc-500 dark:text-zinc-300',
]);
@endphp

<div {{ $attributes->class($classes) }} role="group" aria-label="{{ $label }}" data-flux-option-group>
    <div class="-mx-1.25 my-1.25 h-px" data-flux-option-group-separator-top>
        <flux:separator class="dark:bg-zinc-600!" />
    </div>

    <div class="{{ $labelClasses }}" aria-hidden="true">
        {{ $label }}
    </div>

    {{ $slot }}

    <div class="-mx-1.25 my-1.25 h-px" data-flux-option-group-separator-bottom>
        <flux:separator class="dark:bg-zinc-600!" />
    </div>
</div>
