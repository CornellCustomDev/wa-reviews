<x-forms.select
    label="Guideline"
    variant="listbox"
    searchable
    :options="$form->guidelineOptions"
    placeholder="Select a guideline..."
    wire:model="form.guideline_id"
    required
/>

<flux:radio.group label="Assessment" class="max-w-[600px] mb-4" variant="cards" :indicator="false" size="sm" wire:model="form.assessment" required badge="Required">
    @foreach ($form->assessmentOptions as $option)
        @switch(Str::of($option['value'])->lower()->replace('/', ''))
            @case('pass')
                <flux:radio :value="$option['value']" :label="$option['label']" class="data-[checked]:!bg-wa-pass"/>
                @break
            @case('warn')
                <flux:radio :value="$option['value']" :label="$option['label']" class="data-[checked]:!bg-wa-warn"/>
                @break
            @case('fail')
                <flux:radio :value="$option['value']" :label="$option['label']" class="data-[checked]:!bg-wa-fail"/>
                @break
            @case('na')
                <flux:radio :value="$option['value']" :label="$option['label']" class="data-[checked]:!bg-wa-na"/>
                @break
        @endswitch
    @endforeach
</flux:radio.group>

<x-forms.textarea label="Observations" wire:model="form.description" size="sm" required />

<x-forms.textarea label="Recommendations" wire:model="form.recommendation" size="lg" />

<x-forms.select
    label="Testing Method"
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
