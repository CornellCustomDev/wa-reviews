<flux:modal name="show-guideline" class="md:w-3xl" wire:close="closeGuideline()">
    @if($guideline)
        <h2 class="flex gap-2 mr-8">
            <x-forms.button size="sm" :href="route('guidelines.show', $guideline)">{{ $guideline->number }}</x-forms.button>
            <div class="flex-1 text-cds-gray-950 text-xl font-verdana">{{ $guideline->name }}</div>
        </h2>

        {!! Str::markdown($guideline->notes) !!}
    @endif
</flux:modal>
