@props([
    'variant' => 'outline',
])
@php
$classes = Flux::classes()
    ->add('[:where(&)]:min-w-48 p-[.3125rem]')
    ->add(match($variant) {
        'outline' => 'rounded-lg',
        'cds' => 'rounded-none',
    })
    ->add('shadow-sm')
    ->add('border border-zinc-200 dark:border-zinc-600')
    ->add(match ($variant) {
        'outline' => 'bg-white dark:bg-zinc-700',
        'cds' => 'bg-cds-blue-600',
    })
    ->add('focus:outline-none')
    ;
@endphp

<ui-menu
    {{ $attributes->class($classes) }}
    popover="manual"
    data-flux-menu
>
    {{ $slot }}
</ui-menu>
