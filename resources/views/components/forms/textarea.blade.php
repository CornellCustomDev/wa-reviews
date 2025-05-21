@props([
    'label',
    'toolbar' => 'heading bold italic underline | bullet ordered blockquote | link code ~ undo redo',
    'size' => 'base',
    'description' => null,
    'descriptionTrailing' => null,
    'badge' => null,
])
@php
$badge ??= $attributes->whereStartsWith('required')->isNotEmpty() ? 'Required' : null;
@endphp
<flux:editor :$label :$toolbar :$size :$attributes :$badge :$description :$descriptionTrailing />
