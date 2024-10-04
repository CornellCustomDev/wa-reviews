<table class="table bordered">
    <tr>
        <th>Project</th>
        <td>{{ $scope->project->name }}</td>
    </tr>
    <tr>
        <th>Scope</th>
        <td>{{ $scope->title }}</td>
    </tr>
    <tr>
        <th>URL</th>
        <td>
            <a href="{{ $scope->url }}" target="_blank">{{ $scope->url }}</a>
        </td>
    </tr>
    <tr>
        <th>Siteimprove Report</th>
        <td>
            @if ($scope->siteimprove_url)
                <a href="{{ $scope->siteimprove_url}}" target="_blank">View Report</a>
            @else
                No report available
            @endif
        </td>
    </tr>
    <tr>
        <th>Notes</th>
        <td>{!! Str::markdown($scope->notes) !!}</td>
    </tr>
    <tr>
        <th>Created</th>
        <td>{{ $scope->created_at->toFormattedDateString() }}</td>
    </tr>
</table>


