<div>
    <h1>{{ $project->name }}: Add Issue</h1>

    <form wire:submit="save">
        <x-forms.select
            variant="combobox"
            label="Scope"
            placeholder="Choose scope..."
            wire:model="form.scope_id"
            :options="$this->form->scopeOptions"
            description="Select the page or scope where the issue occurs or <a href='{{ route('project.scope.create', $project) }}'>add a scope</a>."
        />

        @include('livewire.issues.fields', ['form' => $form])

        <x-forms.checkbox
            label="Generate guideline observations with AI"
            wire:model="form.generateGuidelines"
        >
            <x-slot name="description">
                If selected, guideline failures and warnings will be populated by AI based on the target
                and description, and potentially the page source.
            </x-slot>
        </x-forms.checkbox>

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
</div>

<x-slot:sidebarPrimary>
    <h3>Instructions</h3>
    <p>
        If possible, set the scope (or first
        <a href="{{ route('project.scope.create', $project) }}">add a scope</a>, if needed) so there
        is a page associated with the issue.
    </p>
    <p>
        Additional information, like assessments for particular guidelines, will be set via the
        issue details page.
    </p>
    <h4>AI-Generated Observations</h4>
    <p>
        If you select to "Generate guideline observations with AI", it will populate any
        applicable guidelines it identifies.
    </p>
    <p>
        After the issue is created, you can approve or reject
        the AI-generated observations. You can also edit these or add your own observations.
    </p>
</x-slot:sidebarPrimary>
