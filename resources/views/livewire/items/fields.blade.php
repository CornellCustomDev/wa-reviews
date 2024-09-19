<x-cd.form.select label="Guideline" :options="$form->guidelineOptions" wire:model.live="form.guideline_id" />
@if($guideline = $form->guidelines->get($form->guideline_id))
    <p>{{ $guideline->category->name }}: {{ $guideline->criterion->getLongName() }}</p>
@endif

<x-cd.form.radios label="Assessment" :radiobuttons="$form->assessmentOptions" wire:model="form.assessment" inline="true" />

<x-cd.form.text label="Applicability" wire:model="form.description" />

<x-cd.form.text label="Recommendations" wire:model="form.recommendation" />
