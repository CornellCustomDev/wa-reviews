@props([
    'warning',
])
{{-- $warning: array{heading: string, details?: list<string>}, as HTML; sources must escape user content --}}
<flux:callout {{ $attributes->class('mb-4') }} color="amber" icon="exclamation-triangle" role="alert">
    <flux:callout.heading>{!! $warning['heading'] !!}</flux:callout.heading>
    @if (! empty($warning['details']))
        <flux:callout.text class="text-cds-gray-950!">
            <ul>
                @foreach ($warning['details'] as $detail)
                    <li>{!! $detail !!}</li>
                @endforeach
            </ul>
        </flux:callout.text>
    @endif
</flux:callout>
