@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
])

@php
    $classes = Flux::classes()
        ->add('flex size-5 rounded-[3px] outline-0')
        ->add('focus:ring-[3px] ring-offset-2 ring-[#2BA3E3]/40')
        // Label styling
        ->add('[&+ui-label]:font-verdana')
        ->add('[&+ui-label]:font-medium')
        ->add('[&+ui-label]:text-sm')
        ->add('[&+ui-label]:!text-cds-gray-950')
        ->add('[[data-flux-field]:has(>&)>ui-label]:!mb-0') // Required more specificity to override
        // Spacing
        ->add('[[data-flux-field]:has(>&)]:pt-1.5')
        ->add('[[data-flux-field]:has(>&)]:mb-4')
        ->add('[[data-flux-field]:has(>&)]:!gap-x-2')
        ;
@endphp

<flux:with-inline-field :$attributes>
    <ui-checkbox {{ $attributes->class($classes) }} data-flux-control data-flux-checkbox>
        <flux:checkbox.indicator />
    </ui-checkbox>
</flux:with-inline-field>
