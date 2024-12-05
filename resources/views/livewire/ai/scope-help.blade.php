<div>
    <h2>Assistance</h2>

    <livewire:ai.scope-chat :$scope />

    @if ($guideline)
        <hr>

        <h3 style="font-size: 125%">
            <a href="{{ route('guidelines.show', $guideline) }}">Guideline {{ $guideline->number }}</a>: {{ $guideline->name }}</h3>

        <button wire:click="hideGuideline" style="float:right">Hide</button>

        <h4>Guideline Details</h4>
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
