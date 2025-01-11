<div x-show="$wire.guidelineNumber" x-cloak>
    <hr>

    <flux:button size="xs" variant="cds-secondary" wire:click="hideGuideline" style="float:right">Hide</flux:button>

    <h3>Guideline Details</h3>

    @if($guideline)
        <h3 class="text-xl">
            <a href="{{ route('guidelines.show', $guideline) }}">Guideline {{ $guideline->number }}</a>: {{ $guideline->name }}</h3>

        <div class="panel accent-blue fill" style="font-size: 90%">
            {!! Str::markdown($guideline->notes) !!}
        </div>
    @endif
</div>
