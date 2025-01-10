@php
$classes = Flux::classes()
    ->add('size-5')
    ->add('text-sm rounded-[3px] text-zinc-700 dark:text-zinc-800')
    ->add('[ui-checkbox[disabled]_&]:opacity-75 [ui-checkbox[data-checked][disabled]_&]:opacity-50 [ui-checkbox[disabled]_&]:shadow-none [ui-checkbox[data-indeterminate]_&]:shadow-none')
    ->add('[ui-checkbox[data-checked]:not([data-indeterminate])_&>svg:first-child]:block [ui-checkbox[data-indeterminate]_&>svg:last-child]:block')
    ->add([
        'border',
        'border-cds-gray-500 dark:border-white/10',
        '[ui-checkbox[data-checked]_&]:border-cds-gray-900',
        '[ui-checkbox[disabled]_&]:border-zinc-200 dark:[ui-checkbox[disabled]_&]:border-white/5',
        '[print-color-adjust:exact]',
    ])
    ->add([
        'bg-white dark:bg-white/10',
        '[ui-checkbox[data-checked]_&]:bg-[#45729f]',
        '[ui-checkbox[data-checked]_&]:bg-checked',
        '[ui-checkbox[data-checked]_&]:bg-center',
        '[ui-checkbox[data-checked]_&]:bg-no-repeat',
        '[ui-checkbox[data-indeterminate]_&]:bg-[var(--color-accent)]',
        '[ui-checkbox[data-indeterminate]_&]:hover:bg-[var(--color-accent)]',
        '[ui-checkbox[data-indeterminate]_&]:focus:bg-[var(--color-accent)]',
    ])
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-checkbox-indicator>
    <flux:icon.check variant="micro" class="hidden text-[var(--color-accent-foreground)]" />
    <flux:icon.minus variant="micro" class="hidden text-[var(--color-accent-foreground)]" />
</div>
