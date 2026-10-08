@blaze(fold: true)

@props([
    'findable' => false,
])

<ui-tab-group {{ $attributes->class('block') }} @if ($findable) findable @endif data-flux-tab-group>
    {{ $slot }}
</ui-tab-group>
