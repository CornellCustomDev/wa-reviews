@blaze(unsafe: [
    'name', 'label', 'badge',
    'description', 'description:trailing',
    'label:badge', 'label:aside', 'label:trailing',
    'error:name', 'error:bag', 'error:message', 'error:icon', 'error:nested', 'error:deep',
])

@props([
    'countryOrder' => null,
    'countries' => null,
    'country' => null,
    'variant' => 'outline',
    'invalid' => null,
    'value' => null,
    'name' => null,
    'size' => null,
])

@php
$showName = isset($name);
$name ??= $attributes->whereStartsWith('wire:model')->first();
$invalid ??= ($name && $errors->has($name));

if (($wireModel = $attributes->wire('model')) && $wireModel->directive && ! $wireModel->hasModifier('self')) {
    unset($attributes[$wireModel->directive]);
    $wireModel->directive .= '.self';
    $attributes = $attributes->merge([$wireModel->directive => $wireModel->value]);
}

$inputAttributes = $attributes->only([
    'placeholder', 'autocomplete', 'inputmode', 'required', 'disabled', 'readonly',
    'autofocus', 'aria-label', 'aria-labelledby', 'aria-describedby', 'class:input',
]);

$classes = Flux::classes()->add('block w-full min-w-0');

$countryList = collect(\FluxPro\PhoneCountries::all());

if ($countries) {
    $countryList = $countryList->whereIn('iso2', collect($countries)->map(fn ($country) => strtolower($country)));
}

if ($countryOrder) {
    $positions = array_flip(collect($countryOrder)->map(fn ($country) => strtolower($country))->all());
    $countryList = $countryList->sortBy(fn ($country) => $positions[$country['iso2']] ?? PHP_INT_MAX);
}

$initialCountry = $countryList->firstWhere('iso2', strtolower($country ?? ''));
$countryData = $countryList->map(fn ($country) => [
    'iso2' => $country['iso2'],
    'label' => $country['label'],
    'dialCode' => $country['dialCode'],
    'flag' => Flux::flagUrl(strtoupper($country['iso2'])),
])->values();
$countryClasses = Flux::classes()
    ->add('w-auto! dark:shadow-none [ui-phone[data-invalid]_&]:border-red-500! [ui-phone[data-invalid]_&]:shadow-none')
    ->add($variant === 'filled'
        ? 'bg-zinc-800/5! border-0! border-e! border-zinc-200! shadow-none! dark:bg-white/10! dark:disabled:bg-white/[7%]! dark:border-white/10!'
        : 'disabled:border-b-zinc-200 dark:disabled:border-white/5')
    ->add(match ($size) {
        default => 'leading-[1.375rem]',
        'sm', 'xs' => 'leading-[1.125rem]',
    })
    ->add($size === 'xs' ? '[&_[data-flux-flag]]:w-4 [&_.gap-2]:gap-1.5' : '');
$countryTextClasses = $variant === 'filled'
    ? 'text-zinc-700! dark:text-zinc-200!'
    : 'text-zinc-700 [[disabled]_&]:text-zinc-500 dark:text-zinc-300 dark:[[disabled]_&]:text-zinc-400';

$inputAttributes = $inputAttributes->merge([
    'class:input' => 'rounded-s-none! '.($variant === 'outline' ? 'border-s-0 data-invalid:border-s-0' : ''),
]);
@endphp

<flux:with-field :$attributes :$name>
    <ui-phone
        {{ $attributes->except('class:input')->class($classes) }}
        @if ($showName) name="{{ $name }}" @endif
        @if (isset($value)) value="{{ $value }}" @endif
        @if (isset($country)) country="{{ $country }}" @endif
        @if (isset($countries)) countries="{{ collect($countries)->join(',') }}" @endif
        @if (isset($countryOrder)) country-order="{{ collect($countryOrder)->join(',') }}" @endif
        @if (isset($size)) size="{{ $size }}" @endif
        variant="{{ $variant }}"
        utils-url="{{ \Flux\AssetManager::phoneUtilsUrl() }}"
        @if ($invalid) data-invalid aria-invalid="true" @endif
        data-flux-control data-flux-phone
    >
        <flux:input.group wire:ignore :name="null" class="[&>[data-flux-input]]:min-w-0">
            <flux:select variant="custom" searchable name="" :disabled="$attributes->has('disabled')" data-flux-phone-country class="w-auto! shrink-0">
                <flux:select.button :$size :invalid="false" :disabled="$attributes->has('disabled')" :class="$countryClasses" aria-label="{{ __('Change country') }}" data-country-label="{{ __('Change country') }}">
                    <span data-flux-phone-country-initial class="flex min-w-0 flex-1 items-center gap-2 {{ $countryTextClasses }} [[data-flux-phone-ready]_&]:hidden">
                        <flux:flag :country="$initialCountry['iso2'] ?? null" size="xs" />
                        @if ($initialCountry)
                            <span>+{{ $initialCountry['dialCode'] }}</span>
                        @endif
                    </span>
                    <flux:select.selected class="hidden [[data-flux-phone-ready]_&]:flex {{ $countryTextClasses }}">
                        <x-slot:placeholder>
                            <flux:icon.globe-alt variant="micro" :class="$size === 'xs' ? 'size-4' : 'size-5'" />
                        </x-slot:placeholder>
                    </flux:select.selected>
                </flux:select.button>

                <flux:select.options searchable class="min-w-72 max-w-[calc(100vw-2rem)]">
                    <template data-flux-phone-flag-fallback>
                        <flux:flag data-flux-phone-country-flag size="xs" />
                    </template>
                    <template data-flux-phone-option>
                        <flux:select.option variant="custom">
                            <div class="flex min-w-0 items-center gap-2">
                                <flux:flag data-flux-phone-country-flag src="__FLUX_PHONE_FLAG__" size="xs" />
                                <span data-country-name class="truncate [ui-selected_&]:hidden"></span>
                                <span data-country-code class="shrink-0 text-zinc-400 [ui-selected_&]:text-inherit"></span>
                                <span data-country-iso aria-hidden="true" class="sr-only"></span>
                            </div>
                        </flux:select.option>
                    </template>
                </flux:select.options>
            </flux:select>
            <flux:input
                :attributes="$inputAttributes"
                :name="null"
                :$size :$variant :$invalid
                type="tel" autocomplete="tel" inputmode="tel"
                data-flux-phone-input
            />
        </flux:input.group>
        <script type="application/json" data-flux-phone-countries>{!! $countryData->toJson(JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    </ui-phone>
</flux:with-field>

@assets
<flux:phone.scripts />
@endassets
