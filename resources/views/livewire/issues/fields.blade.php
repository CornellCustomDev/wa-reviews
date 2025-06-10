@props([
    'form',
])
<x-forms.input type="text" label="Target" wire:model="form.target" required>
    <x-slot name="description">
        Identify the element causing the issue. You can describe the appearance, use a CSS selector,
        or whatever else communicates exactly what on the page is causing a failure.
    </x-slot>
</x-forms.input>

<x-forms.textarea label="Description" wire:model="form.description" size="sm" required>
    <x-slot name="description">
        What was the observed functionality or behavior that is causing the issue?
    </x-slot>
</x-forms.textarea>

<x-forms.select
    label="Guideline"
    variant="listbox"
    searchable
    :options="$this->getGuidelinesOptions"
    placeholder="Select a guideline..."
    wire:model="form.guideline_id"
>
    <x-slot name="description">
        The success criterion that this issue is assessing.
    </x-slot>
</x-forms.select>

<div x-data="{ guideline_id: $wire.entangle('form.guideline_id') }">
    <flux:radio.group
        label="Assessment"
        wire:model="form.assessment"
        variant="cards"
        :indicator="false"
        description="Select the assessment for the success criterion. See instructions for detailed descriptions."
    >
        @foreach (\App\Enums\Assessment::toSelectArray() as $option)
            @switch(Str::of($option['value'])->lower()->replace('/', ''))
                @case('pass')
                    <flux:radio :value="$option['value']" :label="$option['description']" class="data-checked:bg-wa-pass hover:bg-wa-pass/30"  x-bind:disabled="guideline_id >= 100" />
                    @break
                @case('warn')
                    <flux:radio :value="$option['value']" :label="$option['description']" class="data-checked:bg-wa-warn hover:bg-wa-warn/30"/>
                    @break
                @case('fail')
                    <flux:radio :value="$option['value']" :label="$option['description']" class="data-checked:bg-wa-fail hover:bg-wa-fail/30" x-bind:disabled="guideline_id >= 100" />
                    @break
                @case('na')
                    <flux:radio :value="$option['value']" :label="$option['description']" class="data-checked:bg-wa-na hover:bg-wa-na/30" x-bind:disabled="guideline_id >= 100" />
                    @break
            @endswitch
        @endforeach
    </flux:radio.group>
</div>

<x-forms.textarea label="Recommendations" wire:model="form.recommendation">
    <x-slot name="description">
        Describe what the expected behavior should be. In other words, describe how the element could have been implemented such that you wouldn't be flagging it for an accessibility failure.
    </x-slot>
</x-forms.textarea>

<x-forms.select
    label="Testing method"
    variant="listbox"
    :options="\App\Enums\TestingMethod::toSelectArray()"
    placeholder="Select a testing method..."
    wire:model="form.testing"
    description="Describe how you discovered the issue."
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

<flux:radio.group
    label="User impact level"
    wire:model="form.impact"
    variant="cards"
    :indicator="false"
    description="Select the level of impact on the user. See instructions for detailed descriptions."
>
    @foreach (\App\Enums\Impact::toSelectArray() as $option)
        @switch(Str::of($option['value'])->lower())
            @case('critical')
                <flux:radio :value="$option['value']" :label="$option['value']" class="data-checked:bg-impact-critical hover:bg-impact-critical/30"/>
                @break
            @case('serious')
                <flux:radio :value="$option['value']" :label="$option['value']" class="data-checked:bg-impact-serious hover:bg-impact-serious/30"/>
                @break
            @case('moderate')
                <flux:radio :value="$option['value']" :label="$option['value']" class="data-checked:bg-impact-moderate hover:bg-impact-moderate/30"/>
                @break
            @case('low')
                <flux:radio :value="$option['value']" :label="$option['value']" class="data-checked:bg-impact-low hover:bg-impact-low/30"/>
                @break
        @endswitch
    @endforeach
</flux:radio.group>

