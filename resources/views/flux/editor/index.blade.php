@props([
    'variant' => null,
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'toolbar' => null,
    'invalid' => null,
])

@php
$invalid ??= ($name && $errors->has($name));

$border ??= $invalid ? 'border-red-500' : match ($variant) {
    'cds' => 'rounded-none border-cds-gray-400 border-b-gray-400',
    default => 'rounded-lg border-zinc-200 border-b-zinc-300/80 dark:border-white/10',
};

$classes = Flux::classes()
    ->add('block w-full')
    ->add('shadow-sm [&[disabled]]:shadow-none')
    ->add('border ' . $border)
    ->add('bg-white dark:bg-white/10 dark:[&[disabled]]:bg-white/[7%]')
    ->add('[&_[data-slot=content]]:text-sm')
    ->add('[&_[data-slot=content]]:text-zinc-700 dark:[&_[data-slot=content]]:text-zinc-300')
    ->add('[&[disabled]_[data-slot=content]]:text-zinc-500 dark:[&[disabled]_[data-slot=content]]:text-zinc-400')
    ;
@endphp

<flux:with-field :$attributes>
    <ui-editor {{ $attributes->class($classes) }} aria-label="{{ __('Rich text editor') }}" wire:ignore data-flux-control data-flux-editor>
        <?php if ($slot->isEmpty()): ?>
            <flux:editor.toolbar :$variant :items="$toolbar" />

            <flux:editor.content />
        <?php else: ?>
            {{ $slot }}
        <?php endif; ?>
    </ui-editor>
</flux:with-field>

@assets
<flux:editor.scripts />
<flux:editor.styles />
@endassets
