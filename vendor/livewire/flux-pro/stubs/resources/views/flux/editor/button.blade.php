@props([
    'icon' => null,
])

@php
$classes = Flux::classes()
    ->add('p-0.5 flex items-center justify-center text-sm font-medium rounded touch-manipulation')
    ->add('text-zinc-400 data-[open]:text-zinc-800 hover:text-zinc-800 focus:text-zinc-800 data-[match]:text-zinc-800')
    ->add('disabled:opacity-75 dark:disabled:opacity-75 disabled:cursor-default disabled:pointer-events-none')
    ->add('dark:text-zinc-400 dark:data-[open]:text-white dark:hover:text-white dark:focus:text-white dark:data-[match]:text-white')
    ->add('hover:bg-zinc-200 hover:text-zinc-800')
    ->add('dark:hover:bg-white/10 dark:hover:text-white')
;
@endphp

<flux:with-tooltip :$attributes>
    <button type="button" {{ $attributes->class($classes) }}>
        <?php if ($icon): ?>
            <flux:icon :$icon :variant="$slot->isEmpty() ? 'mini' : 'micro'" />
        <?php endif; ?>

        {{ $slot }}
    </button>
</flux:with-tooltip>
