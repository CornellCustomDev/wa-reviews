<x-cd.form.select label="Guideline" :options="$form->guidelineOptions" wire:model.live="form.guideline_id" />
@if($guideline = $form->guidelines->get($form->guideline_id))
    <p>{{ $guideline->category->name }}: {{ $guideline->criterion->getLongName() }}</p>
@endif

<x-cd.form.radios label="Assessment" :radiobuttons="$form->assessmentOptions" wire:model="form.assessment" inline="true" />

<x-cd.form.text label="Observed functionality (Description)" wire:model="form.description" />

<x-cd.form.select label="Testing Method" :options="$form->testingMethodOptions" wire:model="form.testing_method" />

<x-cd.form.text label="Recommendation for Remediation" wire:model="form.recommendation" />

<x-cd.form.text label="Image Links" wire:model="form.image_links" />

<x-cd.form.checkbox-inline label="CE Issue" :value="true" wire:model="form.content_issue"/>
