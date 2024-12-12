@props([
    'label',
    'id' => $attributes->whereStartsWith('wire:model')->first(),
    'toolbar' => 'heading bold italic underline | bullet ordered blockquote | link code ~ undo redo',
    'variant' => 'cds',
])
<flux:editor :$label :$variant :$attributes class="max-w-[600px] mb-4" />
