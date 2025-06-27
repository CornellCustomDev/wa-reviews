<div>
    <div class="cwd-component align-right">
        <x-forms.button.back :href="route('project.show', $scope->project)" title="Back to Project" />
    </div>

    <h1>{{ $scope->project->name }}: {{ $scope->title }}</h1>

    @include('livewire.scopes.details')

    <flux:tab.group class="mt-8">
        <flux:tabs wire:model.live="tab">
            <flux:tab name="issues" :accent="false">Issues ({{ count($scope->issues) }})</flux:tab>
            <flux:tab name="siteimprove" :accent="false">Siteimprove ({{ $this->siteimproveIssueCount() }})</flux:tab>
{{--            @can('update', $scope)--}}
{{--                <flux:tab name="guidelines" :accent="false">Guidelines</flux:tab>--}}
{{--            @endcan--}}
        </flux:tabs>

        <flux:tab.panel name="issues">
            <livewire:issues.view-issues :$scope />
        </flux:tab.panel>
        <flux:tab.panel name="siteimprove">
            <livewire:scopes.siteimprove-issues :$scope />
        </flux:tab.panel>
{{--        @can('update', $scope)--}}
{{--            <flux:tab.panel name="guidelines" class="pt-6!">--}}
{{--                <livewire:scopes.scope-guidelines :$scope />--}}
{{--            </flux:tab.panel>--}}
{{--        @endcan--}}

    </flux:tab.group>
</div>

<x-slot:sidebarPrimary>
    <livewire:scopes.scope-sidebar :$scope />
</x-slot:sidebarPrimary>
