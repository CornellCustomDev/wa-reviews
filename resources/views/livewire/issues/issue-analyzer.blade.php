<div>
    @can('update', $issue)
        <div class="mb-4">
            <x-forms.button wire:click="populateGuidelines()" icon="check">
                Populate Guidelines
            </x-forms.button>
            <span wire:loading.delay wire:target="populateGuidelines"> Analyzing...</span>
        </div>
    @endcan

    <div wire:show="showFeedback" x-transition.duration.500ms wire:cloak>
        <flux:card size="sm" class="flex bg-cds-blue-50!">
            <div class="flex-1">
                <h3 class="h5">AI Response</h3>
                {!! Str::of(htmlentities($feedback))->markdown() !!}
            </div>
            <div class="-mx-2">
                <flux:button wire:click="$toggle('showFeedback')" variant="ghost" size="sm" icon="x-mark" inset="top right bottom" />
            </div>
        </flux:card>
    </div>
</div>
