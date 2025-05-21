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

        <flux:tab.panel name="issues" class="pt-6!">
            <livewire:issues.view-issues :$scope />
        </flux:tab.panel>
        <flux:tab.panel name="siteimprove" class="pt-6!">
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
    <div x-data="{ showChat: false }">
        <h2>AI Assistance</h2>

        <div class="mb-4">
            <x-forms.button x-on:click="showChat = ! showChat" icon="chat-bubble-left-right" x-bind:class="{ 'secondary': showChat }">
                <span><span x-show="showChat">Hide </span>Chat</span>
            </x-forms.button>
        </div>

        <div x-show="showChat" x-cloak>
            <hr>
            <livewire:scopes.scope-chat :$scope />
        </div>
    </div>
</x-slot:sidebarPrimary>
