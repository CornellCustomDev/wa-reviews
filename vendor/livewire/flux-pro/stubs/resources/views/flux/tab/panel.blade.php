@blaze(fold: true)

@aware([
    'findable' => false,
])

@props([
    'selected' => false,
    'name' => null,
])

@php
$classes = Flux::classes()
    ->add('[:where(&)]:pt-8')
    ->add('[&[hidden=until-found]]:absolute [&[hidden=until-found]]:opacity-0 [&[hidden=until-found]]:pointer-events-none')
;

if ($name) {
    $attributes = $attributes->merge([
        'name' => $name,
        'wire:key' => $name,
    ]);
}
@endphp

<div {{ $attributes->class($classes)->merge(['data-selected' => $selected, 'hidden' => $selected ? false : ($findable ? 'until-found' : true)]) }} data-flux-tab-panel>
    {{ $slot }}
</div>
