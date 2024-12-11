<div>
    @if ($this->siteimproveUrl())
        <flux:subheading class="mb-4">
            <a href="{{ $this->siteimproveUrl() }}" target="_blank">
                <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700" />
                View Siteimprove Page Report</a>
        </flux:subheading>

        @if (!empty($this->siteImproveIssues()))
            <table class="table striped bordered">
                <thead>
                    <tr>
                        <th>Siteimprove Issue</th>
                        <th>Related Guidelines</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->siteimproveIssues() as $issue)
                        <tr>
                            <td>
                                {{ $issue['title'] }}
                                ({{ $issue['occurrences'] }} {{ Str::plural('occurence', $issue['occurrences']) }},
                                <a href="{{ $this->siteimproveUrl() }}#/sia-r{{ $issue['rule_id'] }}/failed"
                                   target="_blank">Issue Detail
                                    <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
                                </a>
                                )
                            </td>
                            <td>
                                @foreach ($this->siteimproveRelatedGuidelines($issue['rule_id']) as $guideline)
                                    <x-forms.button
                                        title="View Guideline {{ $guideline->number }}"
                                        size="xs"
                                        x-on:click.prevent="$dispatch('show-guideline', {number: {{ $guideline->number }} })"
                                    >{{ $guideline->number }}</x-forms.button>
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
        No Siteimprove page report available
    @endif
</div>
