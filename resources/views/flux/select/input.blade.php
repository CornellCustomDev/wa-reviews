@aware([ 'placeholder' ])

@props([
    'placeholder' => null,
    'invalid' => false,
    'size' => null,
])

<flux:input :$invalid :$size :$placeholder :$attributes>
    <x-slot name="iconTrailing">
        <flux:button variant="cds" tabindex="-1" class="-mr-2 h-10 w-8 bg-cds-blue-600/95 [[disabled]_&]:pointer-events-none [[disabled]_&]:bg-cds-blue-600/60">
            <flux:icon.chevron-up-down class="text-white [[disabled]_&]:!text-cds-gray-50 dark:text-white/60 dark:[[data-flux-input]:hover_&]:text-white dark:[[disabled]_&]:!text-white/40" />
        </flux:button>
    </x-slot>
</flux:input>
