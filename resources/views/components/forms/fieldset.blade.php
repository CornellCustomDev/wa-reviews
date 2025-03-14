@props([
    'legend',
    'description' => null,
])

@php
    $descr_id = 'desc-' . Str::random(8);
@endphp

@if ($description)
<flux:fieldset legend="{{ $legend }}" aria-describedby="{{ $descr_id }}" class="mb-4 semantic">
    <div class="ml-4 -mr-4">
        <p id="{{ $descr_id }}">{{ $description }}</p>
        {{ $slot }}
    </div>
</flux:fieldset>
@else
<flux:fieldset legend="{{ $legend }}" class="mb-4 semantic">
    <div class="ml-4 -mr-4">
        {{ $slot }}
    </div>
</flux:fieldset>
@endif
