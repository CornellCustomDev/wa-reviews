<div>
    <flux:subheading class="mb-4">
        @if ($project->siteimprove_url)
            <flux:icon.arrow-right-start-on-rectangle class="inline-block -mt-1" variant="mini"/>
            <a href="{{ $project->siteimprove_url }}" target="_blank">View Siteimprove Report</a>
        @else
            No Siteimprove report available
        @endif
    </flux:subheading>

    @if ($siteimprovePages)
        <table class="table striped bordered">
            <thead>
            <tr>
                <th>URL</th>
                <th>Issues</th>
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
                            <x-forms.button.view :href="route('scope.show', $scope)" size="sm" icon="eye">View Scope</x-forms.button.view>
                        @else
                            <x-forms.button :href="route('project.scope.create', ['project' => $project, 'url' => $page['url']])" size="sm" icon="plus-circle">Add to Scope</x-forms.button>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>No pages with issues found</p>
    @endif
</div>
