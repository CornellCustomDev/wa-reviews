@blaze(fold: true)

@php
$classes = Flux::classes()
    ->add('p-[.3125rem]')
    ->add('overflow-y-auto overscroll-y-none')
    ->add('bg-white dark:bg-zinc-700')
    ;
@endphp

<ui-options {{ $attributes->class($classes) }} data-flux-command-items>
    {{ $slot }}

    <flux:command.empty>{!! __('No results found') !!}</flux:command.empty>
</ui-options>
