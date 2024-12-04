<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('scope.edit', $scope) }}" title="Edit" />
    </div>

    <h1>{{ $scope->project->name }}: {{ $scope->title }}</h1>

    @include('livewire.scopes.details')

    <div style="margin-bottom: 2em">
        <livewire:issues.view-issues :$scope />
    </div>

    @if ($this->siteimproveIssues())
        <div style="margin-bottom: 2em">
            <h2>Siteimprove Issues</h2>
            <ul>
                @foreach ($this->siteimproveIssues() as $issue)
                    <li>
                        <a href="{{ $this->siteimproveUrl() }}#/sia-r{{ $issue['rule_id'] }}/failed"
                           target="_blank">{{ $issue['title'] }}</a> ({{ $issue['occurrences'] }} occurrences)

                        @foreach ($this->siteimproveRelatedGuidelines($issue['rule_id']) as $guideline)
                            <x-forms.link-button
                                route="#" title="{{ $guideline->number }}"
                                x-data x-on:click.prevent="$dispatch('show-guideline', {number: {{ $guideline->number }} })"
                            />
                        @endforeach
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="margin-bottom: 2em;">
        <h2>Guidelines Review</h2>
        <livewire:scopes.scope-guidelines :$scope />
    </div>
</div>

<x-slot:sidebarPrimary>
    <livewire:ai.scope-help :$scope />
</x-slot:sidebarPrimary>
