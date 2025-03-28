@props([
    'label',
])

<div data-cds-field-display {{ $attributes }}>
    <flux:heading level="3">
        {{ $label }}
    </flux:heading>
    <div>{{ $slot }}</div>
</div>
