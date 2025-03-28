@props([
    'label',
])

<div data-cds-field-display {{ $attributes }}>
    @if ($label)
        <flux:heading level="3">
            {{ $label }}
        </flux:heading>
    @endif
    <div>{{ $slot }}</div>
</div>
