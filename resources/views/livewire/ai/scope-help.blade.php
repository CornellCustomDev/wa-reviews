<div>
    <h2>Assistance</h2>

    <livewire:ai.scope-chat :$scope />

    @if ($guideline)
        <hr>

        <flux:button size="xs" variant="cds-secondary" wire:click="hideGuideline" style="float:right">Hide</flux:button>

        <h3>Guideline Details</h3>

        <h3 class="text-xl">
            <a href="{{ route('guidelines.show', $guideline) }}">Guideline {{ $guideline->number }}</a>: {{ $guideline->name }}</h3>

        <div class="panel accent-blue fill" style="font-size: 90%">
            {!! Str::markdown($guideline->notes) !!}
        </div>
    @endif

    <div x-show="$wire.response != null && $wire.response != ''" x-cloak>
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <pre>{{ $response }}</pre>
        </div>
    </div>
</div>
