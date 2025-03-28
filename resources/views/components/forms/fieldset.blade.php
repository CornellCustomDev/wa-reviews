@props([
    'legend',
    'description' => null,
])

<flux:fieldset :$legend :$description :$attributes>
    {{ $slot }}
</flux:fieldset>
