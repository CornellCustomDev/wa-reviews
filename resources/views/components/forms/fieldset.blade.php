@props([
    'legend',
    'description' => null,
])

{{-- @php
    $descr_id = 'desc-' . Str::random(8);
@endphp --}}


<flux:fieldset :$legend :$description :$attributes>
    {{ $slot }}
</flux:fieldset>
