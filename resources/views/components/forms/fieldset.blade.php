@props([
    'legend',
    'description' => null,
])

@php
    $descr_id = 'desc-' . Str::random(8);
@endphp

@if ($description)
<flux:fieldset aria-describedby="{{ $descr_id }}" class="mb-4">
    <legend class="font-sans font-semibold text-cds-gray-900 text-[15px]">{{ $legend }}</legend>
    <p id="{{ $descr_id }}">{{ $description }}</p>
    {{ $slot }}
</flux:fieldset>
@else
<flux:fieldset class="mb-4">
    <legend class="font-sans font-semibold text-cds-gray-900 text-[15px]">{{ $legend }}</legend>
        {{ $slot }}
</flux:fieldset>
@endif
