<flux:modal name="confirm-recommendation" class="md:w-3xl" wire:close="closeConfirmRecommendation()">
    <p class="mb-4">Do you wish to update the issue with the AI recommendation for guideline {{ $guidelineNumber }}?</p>

    <x-forms.button
        wire:click="$dispatch('ai-accepted', {guidelineNumber: '{{ $guidelineNumber }}'})"
        icon="check"
        class="mr-2"
    >
        Yes
    </x-forms.button>
    <x-forms.button
        wire:click="closeConfirmRecommendation"
        icon="x-mark"
        class="secondary"
    >
        Cancel
    </x-forms.button>
</flux:modal>
