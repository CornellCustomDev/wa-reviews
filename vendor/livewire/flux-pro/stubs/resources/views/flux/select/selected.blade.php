@blaze(fold: true)

@props([
    'placeholder' => null,
    'suffix' => null,
    'max' => 1,
])

@php
    $classes = Flux::classes()
        ->add('min-w-0 flex gap-2 text-start flex-1 text-zinc-700')
        ->add('[[disabled]_&]:text-zinc-500 dark:text-zinc-300 dark:[[disabled]_&]:text-zinc-400');
@endphp

<ui-selected x-ignore wire:ignore {{ $attributes->class($classes) }}>
    <template name="placeholder">
        <span class="truncate text-zinc-400 [[disabled]_&]:text-zinc-400/70 dark:text-zinc-400 dark:[[disabled]_&]:text-zinc-500" data-flux-select-placeholder>
            {{ $placeholder }}
        </span>
    </template>

    <template name="option">
        <div class="truncate min-w-0"><slot></slot></div>
    </template>

    <template name="overflow" max="{{ $max }}" >
        <div><slot name="count"></slot> {{ $suffix ?? __('selected') }}</div>
    </template>
</ui-selected>
