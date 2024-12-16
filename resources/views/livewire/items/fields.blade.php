<x-forms.select
    label="Guideline"
    :options="$form->guidelineOptions"
    placeholder="Select a guideline..."
    wire:model="form.guideline_id"
/>

<x-forms.radios label="Assessment" :values="$form->assessmentOptions" wire:model="form.assessment" />

<x-forms.textarea label="Observations" wire:model="form.description" size="sm" />

<x-forms.textarea label="Recommendations" wire:model="form.recommendation" size="lg" />

<x-forms.select
    label="Testing Method"
    :options="$form->testingMethodOptions"
    placeholder="Select a testing method..."
    wire:model="form.testing"
/>
