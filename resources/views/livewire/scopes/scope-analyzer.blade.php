<div>
    <div class="mb-4">
        <x-forms.button wire:click="recommendGuidelines()" icon="sparkles">
            Analyze Scope
        </x-forms.button>
        <span wire:loading.delay wire:target="recommendGuidelines"> Analyzing...</span>
    </div>

    <div wire:stream="streamedResponse" wire:show="streaming" role="status" aria-live="polite"
         aria-atomic="false">{{ $streamedResponse }}</div>

    <div wire:show="showFeedback" x-transition.duration.500ms wire:cloak>
        <flux:card size="sm" class="flex bg-cds-blue-50!">
            <div class="flex-1">
                <h3 class="h5">AI Response</h3>
                {!! Str::of(htmlentities($feedback))->markdown() !!}
            </div>
            <div class="-mx-2">
                <flux:button wire:click="$toggle('showFeedback')" variant="ghost" size="sm" icon="x-mark"
                             inset="top right bottom"/>
            </div>
        </flux:card>
    </div>
</div>
