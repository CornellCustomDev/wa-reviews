<div>
    @if ($project->siteimprove_url)
        <flux:subheading class="mb-4">
            <flux:icon.arrow-right-start-on-rectangle class="inline-block -mt-1" variant="mini"/>
            <a href="{{ $project->siteimprove_url }}" target="_blank">View Siteimprove Report</a>
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
                            <a href="{{ $page['page_report'] }}" target="_blank">Page Report</a>
                            <flux:icon.arrow-right-start-on-rectangle class="inline-block -mt-1" variant="mini"/>
                            )
                        </td>
                        <td>
                            @if($scope = $this->pageInScope($page['url']))
    {{--                            view scope --}}
                                <x-forms.button.view
                                    :href="route('scope.show', $scope)"
                                    size="xs" icon="eye" title="View scope {{ $scope->id }}" />
                            @else
                                <x-forms.button
                                    :href="route('project.scope.create', ['project' => $project, 'url' => $page['url']])"
                                    size="xs" icon="plus-circle" title="Add Row {{ $loop->iteration }} to Scope" />
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
