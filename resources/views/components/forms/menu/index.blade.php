@props([
    'variant' => 'cds',
])
<flux:menu :$variant :$attributes>
    {{ $slot }}
</flux:menu>
