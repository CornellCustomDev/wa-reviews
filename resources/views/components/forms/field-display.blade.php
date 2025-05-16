@props([
    'label',
    'variation' => 'default',
])

<div data-cds-field-display {{ $attributes }}>
    @if($variation === 'inline')
        <flux:heading level="3" class="inline-block">
            {{ $label }}:
        </flux:heading>
        <div class="inline-block">
            {{ $slot }}
        </div>
    @else
        <flux:heading level="3">
            {{ $label }}
        </flux:heading>
        <div>{{ $slot }}</div>
    @endif
</div>
