@props([
    'legend',
    'description' => null,
])

@php
    $descr_id = 'desc-' . Str::random(8);
@endphp

@if ($description)
<flux:fieldset legend="{{ $legend }}" aria-describedby="{{ $descr_id }}" class="mb-8 semantic">
    <p id="{{ $descr_id }}">{{ $description }}</p>
    {{ $slot }}
</flux:fieldset>
@else
<flux:fieldset legend="{{ $legend }}" class="mb-8 semantic">
        {{ $slot }}
</flux:fieldset>
@endif
