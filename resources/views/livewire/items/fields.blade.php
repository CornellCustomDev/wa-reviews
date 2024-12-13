<x-cd.form.select label="Guideline" :options="$form->guidelineOptions" wire:model.live="form.guideline_id" />

<x-cd.form.radios label="Assessment" :radiobuttons="$form->assessmentOptions" wire:model="form.assessment" inline="true" />

<x-forms.textarea label="Observations" wire:model="form.description" size="sm" />

<x-forms.textarea label="Recommendations" wire:model="form.recommendation" size="lg" />

<x-cd.form.select label="Testing Method" :options="$form->testingMethodOptions" wire:model="form.testing" />
