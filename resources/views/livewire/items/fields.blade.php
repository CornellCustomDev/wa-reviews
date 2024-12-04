<x-cd.form.select label="Guideline" :options="$form->guidelineOptions" wire:model.live="form.guideline_id" />
@if($guideline = $form->guidelines->get($form->guideline_id))
    <p>{{ $guideline->category->name }}: {{ $guideline->criterion->getLongName() }}</p>
@endif

<x-cd.form.radios label="Assessment" :radiobuttons="$form->assessmentOptions" wire:model="form.assessment" inline="true" />

<x-forms.textarea label="Observations" wire:model="form.description" />

<x-forms.textarea label="Recommendations" wire:model="form.recommendation" />

<x-cd.form.select label="Testing Method" :options="$form->testingMethodOptions" wire:model="form.testing" />
