<div>
    @if ($project->siteimprove_url)
        <flux:subheading class="mb-4">
            <a href="{{ $project->siteimprove_url }}" target="_blank">
                <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700" />
            View Siteimprove Report</a>
        </flux:subheading>

        @if ($siteimprovePages)
            <table class="table striped bordered">
                <thead>
                <tr>
                    <th>URL</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($siteimprovePages as $page)
                    <tr>
                        <td class="align-middle">
                            {{ $page['url'] }}
                            ({{ $page['issues'] }} {{ Str::plural('issue', $page['issues']) }},
                            <a href="{{ $page['page_report'] }}" target="_blank">
                                Page Report
                                <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
                            </a>
                            )
                        </td>
                        <td>
                            @if($scope = $this->pageInScope($page['url']))
                                <x-forms.button.view
                                    :href="route('scope.show', $scope)"
                                    size="sm" icon="eye" title="View scope {{ $scope->id }}" />
                            @else
                                <x-forms.button
                                    :href="route('project.scope.create', ['project' => $project, 'url' => $page['url']])"
                                    variant="cds-secondary"
                                    size="sm" icon="plus" title="Add Row {{ $loop->iteration }} to Scope" />
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>No pages with issues found</p>
        @endif
    @else
        No Siteimprove report available
    @endif
</div>
