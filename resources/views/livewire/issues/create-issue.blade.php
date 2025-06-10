<div>
    <h1>{{ $this->scope->title }}: Add Issue</h1>

    @if($this->scope->url)
        <x-forms.field-display label="URL" variation="inline">
            <a href="{{ $this->scope->url }}" target="_blank">{{ $this->scope->url }}</a>
        </x-forms.field-display>
    @endif

    <form wire:submit="save">
        @include('livewire.issues.fields', ['form' => $form])

        <x-forms.button.submit-group submit-name="Add Issue" />
    </form>
</div>

<x-slot:sidebarPrimary>
    <h3>Instructions</h3>
    <p>
        An Issue identifies an accessibility problem.
    </p>
    <x-forms.field-display label="Target" variation="inline">
        Describe <strong>what</strong> exactly is causing the issue.
    </x-forms.field-display>
    <x-forms.field-display label="Description" variation="inline">
        Describe <strong>why</strong> there is an issue.
    </x-forms.field-display>
</x-slot:sidebarPrimary>
