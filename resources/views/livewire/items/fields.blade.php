<x-forms.select
    label="Guideline"
    variant="listbox"
    searchable
    :options="$form->guidelineOptions"
    placeholder="Select a guideline..."
    wire:model="form.guideline_id"
    required
/>

<div x-data="{ guideline_id: $wire.entangle('form.guideline_id') }">
    <flux:radio.group label="Assessment" variant="cards" :indicator="false" size="sm" wire:model="form.assessment" required badge="Required">
        @foreach ($form->assessmentOptions as $option)
            @switch(Str::of($option['value'])->lower()->replace('/', ''))
                @case('pass')
                    <flux:radio :value="$option['value']" :label="$option['label']" class="data-checked:bg-wa-pass hover:bg-wa-pass/30" />
                    @break
                @case('warn')
                    <flux:radio :value="$option['value']" :label="$option['label']" class="data-checked:bg-wa-warn hover:bg-wa-warn/30"/>
                    @break
                @case('fail')
                    <flux:radio :value="$option['value']" :label="$option['label']" class="data-checked:bg-wa-fail hover:bg-wa-fail/30" x-bind:disabled="guideline_id >= 100"/>
                    @break
                @case('na')
                    <flux:radio :value="$option['value']" :label="$option['label']" class="data-checked:bg-wa-na hover:bg-wa-na/30"/>
                    @break
            @endswitch
        @endforeach
</flux:radio.group>
</div>

<x-forms.textarea label="Observations" wire:model="form.description" size="sm" required />

<x-forms.textarea label="Recommendations" wire:model="form.recommendation" size="lg" />

<x-forms.select
    label="Testing method"
    variant="listbox"
    :options="$form->testingMethodOptions"
    placeholder="Select a testing method..."
    wire:model="form.testing"
/>

<x-forms.checkbox
    label="Content entry issue"
    wire:model="form.content_issue"
/>

<x-forms.image-upload
    label="Image examples"
    wire:model.live="form.images"
    :existing-images="$this->form->image_links"
/>

<x-forms.radios label="User impact level" wire:model="form.impact" :values="$form->impactOptions" />
