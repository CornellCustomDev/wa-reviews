<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('scope.edit', $scope) }}" title="Edit" />
    </div>

    <h1>{{ $scope->project->name }}: {{ $scope->title }}</h1>

    @include('livewire.scopes.details')

    <div style="margin-bottom: 2em;">
        <h2>Guidelines Review</h2>
        <livewire:scopes.scope-guidelines :$scope />
    </div>

    <livewire:issues.view-issues :$scope />
</div>

<x-slot:sidebarPrimary>
    <livewire:ai.scope-help :$scope />
</x-slot:sidebarPrimary>
