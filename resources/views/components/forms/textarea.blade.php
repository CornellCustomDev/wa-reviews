@props([
    'label',
    'toolbar' => 'heading bold italic underline | bullet ordered blockquote | link code ~ undo redo',
    'variant' => 'cds',
    'size' => 'base',
    'badge' => null,
    'description' => null,
])
@php
$classes = Flux::classes()
    ->add('max-w-[600px]')
    ->add('[&_[data-slot=content]]:' . match ($size) {
        'base' => 'min-h-44',
        'sm' => 'min-h-24',
        'lg' => 'min-h-64',
    })
    ->add('[[data-flux-field]:has(>&)]:mb-4')
    ;

$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp
<flux:editor :$label :$variant :$toolbar :attributes="$attributes->class($classes)" :$badge description="{{ $description }}" />
