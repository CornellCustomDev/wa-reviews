<div>
    @if ($this->siteimproveUrl())
        <flux:subheading class="mb-4">
            <a href="{{ $this->siteimproveUrl() }}" target="_blank">
                View Siteimprove Page Report</a>
            <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
        </flux:subheading>

        @if (!empty($this->siteImproveIssues()))
            <table class="table striped bordered">
                <thead>
                    <tr>
                        <th>Siteimprove Issue</th>
                        <th>SIA ID</th>
                        <th>Related Guidelines</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->siteimproveIssues() as $issue)
                        <tr>
                            <td>
                                {{ $issue['title'] }}
                                <span class="text-nowrap">
                                ({{ $issue['occurrences'] }} {{ Str::plural('occurrence', $issue['occurrences']) }},
                                <a href="{{ $this->siteimproveUrl() }}#/sia-r{{ $issue['rule_id'] }}/failed"
                                   target="_blank" title="View Siteimprove report for {{ $issue['title'] }}">
                                    Issue Detail
                                    <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
                                </a>
                                )
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('sia-rules.show', $issue['rule_id']) }}">{{ $issue['rule_id'] }}</a>
                            </td>
                            <td>
                                @foreach ($this->siaRelatedGuidelines($issue['rule_id']) as $guideline)
                                    <flux:dropdown wire:key="$issue->id .':'. $guideline->number" gap="1">
                                        <x-forms.button size="xs">{{ $guideline->number }}</x-forms.button>
                                        <x-forms.menu>
                                            <x-forms.menu.item icon="eye" wire:click="$dispatch('show-guideline', {number: {{ $guideline->number }} })">
                                                View Guideline
                                            </x-forms.menu.item>
                                            @if($existingIssueId = $this->siaRelatedIssues($issue['rule_id']))
                                                <x-forms.menu.item icon="eye" wire:click="$dispatch('show-issue', {issue: {{ $existingIssueId }} })">
                                                    View Issue
                                                </x-forms.menu.item>
                                            @else
                                                @can('create', [\App\Models\Issue::class, $scope->project])
                                                    <x-forms.menu.item icon="plus" wire:click="$dispatch('create-issue', {rule: {{ $issue['rule_id'] }}, guideline: {{ $guideline->id }} })">
                                                        New Issue
                                                    </x-forms.menu.item>
                                                @endcan
                                            @endif
                                        </x-forms.menu>
                                    </flux:dropdown>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No issues found</p>
        @endif
    @else
        <flux:separator class="mb-2" />
        No Siteimprove page report available
    @endif
</div>
