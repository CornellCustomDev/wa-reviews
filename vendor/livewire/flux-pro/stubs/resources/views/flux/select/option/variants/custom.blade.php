@blaze(fold: true, unsafe: ['icon:variant'])

@php $iconVariant ??= $attributes->pluck('icon:variant'); @endphp

@aware([ 'indicator' ])

@props([
    'iconVariant' => 'mini',
    'filterable' => null,
    'description' => null,
    'indicator' => null,
    'loading' => null,
    'avatar' => null,
    'label' => null,
    'value' => null,
    'icon' => null,
])

@php
// The icon prop is ignored when an avatar is present...
if ($avatar) $icon = null;

$classes = Flux::classes()
    ->add('group/option overflow-hidden data-hidden:hidden group flex items-start px-2 w-full focus:outline-hidden')
    ->add($description ? 'py-2.5' : 'py-1.5')
    ->add('rounded-md')
    ->add('text-start text-sm font-medium select-none')
    ->add('text-zinc-800 data-active:bg-zinc-100 [&[disabled]]:text-zinc-400 dark:text-white dark:data-active:bg-zinc-600 dark:[&[disabled]]:text-zinc-400')
    ;

$iconClasses = Flux::classes()
    ->add('me-2 [:where(&)]:text-zinc-400 [:where(&)]:dark:text-white/60')
    ->add($iconVariant === 'outline' ? 'size-5' : null)
    ->add($attributes->pluck('icon:class'))
    ;

$avatarAttributes = Flux::attributesAfter('avatar:', $attributes, [
    'class' => 'me-2 [ui-selected_&]:size-6',
    'size' => 'xs',
    'name' => $label,
    'src' => $avatar,
    'circle' => true,
]);

$livewireAction = $attributes->whereStartsWith('wire:click')->isNotEmpty();
$alpineAction = $attributes->whereStartsWith('x-on:click')->isNotEmpty();

$loading ??= $loading ?? $livewireAction;

if ($loading) {
    $attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);
}
@endphp

<ui-option
    @if ($value !== null) value="{{ $value }}" @endif
    @if ($value) wire:key="{{ $value }}" @endif
    @if ($label !== null) label="{{ $label }}" @endif
    @if ($filterable === false) filter="manual" @endif
    @if ($livewireAction || $alpineAction) action @endif
    {{ $attributes->class($classes) }}
    data-flux-option
>
    <div class="flex min-w-0 {{ $description ? 'items-start [ui-selected_&]:items-center' : 'items-center' }}">
        <div class="flex items-center">
            <div class="w-6 min-h-5 flex items-center shrink-0 [ui-selected_&]:hidden">
                <flux:select.indicator :variant="$indicator" />
            </div>

            <?php if ($avatar): ?>
                @unblaze(scope: ['attributes' => $avatarAttributes->getAttributes()])
                    <flux:avatar :attributes="new \Illuminate\View\ComponentAttributeBag($scope['attributes'])" />
                @endunblaze
            <?php elseif (is_string($icon) && $icon !== ''): ?>
                <flux:icon :$icon :variant="$iconVariant" :class="$iconClasses" />
            <?php elseif ($icon): ?>
                {{ $icon }}
            <?php endif; ?>
        </div>

        <div class="flex min-w-0 flex-col gap-1">
            <div class="[ui-selected_&]:truncate {{ $avatar ? 'whitespace-nowrap' : '' }}">
                {{ $slot->isNotEmpty() ? $slot : $label }}
            </div>

            <?php if ($description): ?>
                <flux:text class="text-xs {{ $avatar ? 'whitespace-nowrap' : '' }} [ui-selected_&]:hidden">{{ $description }}</flux:text>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($loading): ?>
        <flux:icon.loading class="hidden [[data-flux-loading]>&]:block ms-auto text-zinc-400 [[data-flux-menu-item]:hover_&]:text-current" variant="micro" />
    <?php endif; ?>
</ui-option>
