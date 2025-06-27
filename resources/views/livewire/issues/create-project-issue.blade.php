<div>
    <h1>{{ $project->name }}: Add Issue</h1>

    <form wire:submit="save">
        <x-forms.select
            variant="combobox"
            label="Scope"
            placeholder="Choose scope..."
            wire:model.live="form.scope_id"
            :options="$this->scopeOptions()"
        >
            <x-slot name="description">
                Select the page or scope where the issue occurs
                @can('create', [\App\Models\Scope::class, $project])
                    or <a href='#' wire:click.prevent='addScope()'>add a scope</a>.
                @endcan
            </x-slot>
        </x-forms.select>

        @include('livewire.issues.fields', ['form' => $form])

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
    <livewire:scopes.add-scope :project="$project" />
</div>

<x-slot:sidebarPrimary>
    <h3>Instructions</h3>
    <p>
        If possible, set the scope (or first
        <a href="{{ route('project.scope.create', $project) }}">add a scope</a>, if needed) so there
        is a page associated with the issue.
    </p>

    @include('livewire.issues.instructions')

    <flux:separator class="mb-4 clear-both" />

    <livewire:issues.issue-form-analyzer :$form />

</x-slot:sidebarPrimary>
