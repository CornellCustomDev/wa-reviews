<div x-show="$wire.guidelineNumber" x-cloak>
    <hr>

    <x-forms.button size="xs" class="secondary float-right" wire:click="hideGuideline">Hide</x-forms.button>

    <h3>Guideline Details</h3>

    @if($guideline)
        <h3 class="text-xl">
            <a href="{{ route('guidelines.show', $guideline) }}">Guideline {{ $guideline->number }}</a>: {{ $guideline->name }}</h3>

        <div class="panel accent-blue fill" style="font-size: 90%">
            {!! Str::markdown($guideline->notes) !!}
        </div>
    @endif
</div>
