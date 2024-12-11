<div>
    <h1>{{ $scope->project->name }}: {{ $scope->title }}</h1>

    @include('livewire.scopes.details')

    <flux:tab.group>
        <flux:tabs wire:model="tab">
            <flux:tab name="issues">Issues ({{ count($scope->issues) }})</flux:tab>
            <flux:tab name="siteimprove">Siteimprove ({{ $this->siteimproveIssueCount() }})</flux:tab>
            <flux:tab name="guidelines">Guidelines</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="issues" class="!pt-6">
            <livewire:issues.view-issues :$scope />
        </flux:tab.panel>
        <flux:tab.panel name="siteimprove" class="!pt-6">
            <livewire:scopes.siteimprove-issues :$scope />
        </flux:tab.panel>
        <flux:tab.panel name="guidelines" class="!pt-6">
            <livewire:scopes.scope-guidelines :$scope />
        </flux:tab.panel>
    </flux:tab.group>
</div>

<x-slot:sidebarPrimary>
    <livewire:ai.scope-help :$scope />
</x-slot:sidebarPrimary>
