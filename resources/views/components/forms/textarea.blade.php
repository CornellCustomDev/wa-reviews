@props([
    'label',
    'id' => $attributes->whereStartsWith('wire:model')->first(),
    'toolbar' => 'heading bold italic underline | bullet ordered blockquote | link code ~ undo redo',
    'variant' => 'cds',
    'size' => 'base',
])
@php
$classes = Flux::classes()
    ->add('max-w-[600px] mb-4')
    ->add('[&_[data-slot=content]]:last:!mb-0')
    ->add('[&_[data-slot=content]]:' . match ($size) {
        'base' => 'min-h-44',
        'sm' => 'min-h-24',
        'lg' => 'min-h-64',
    })
@endphp
<flux:editor :$label :$variant :attributes="$attributes->class($classes)" />
