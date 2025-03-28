@props([
    'label',
    'toolbar' => 'heading bold italic underline | bullet ordered blockquote | link code ~ undo redo',
    'size' => 'base',
    'badge' => null,
])
@php
$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp
<flux:editor :$label :$toolbar :$size :$attributes :$badge />
