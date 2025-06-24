<div>
    <h1>{{ $scope->title }}: Add Issue</h1>

    @if($scope->url)
        <x-forms.field-display label="URL" variation="inline">
            <a href="{{ $scope->url }}" target="_blank">{{ $scope->url }}</a>
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

    @include('livewire.issues.instructions')

    <flux:separator class="mb-4 clear-both" />

    <livewire:issues.issue-form-analyzer :$scope :$form />
</x-slot:sidebarPrimary>
