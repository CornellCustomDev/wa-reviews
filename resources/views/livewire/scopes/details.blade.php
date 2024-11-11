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
        <th>Siteimprove Page Report</th>
        <td>
            @if ($this->siteimproveUrl())
                <a href="{{ $this->siteimproveUrl() }}" target="_blank">View Report</a> ({{ $this->siteimproveIssueCount() }} {{ Str::plural('issue', $this->siteimproveIssueCount()) }} )
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

@if ($this->siteimproveIssues())
    <div style="margin-bottom: 2em">
        <h2>Siteimprove Issues</h2>
        <ul>
            @foreach ($this->siteimproveIssues() as $issue)
                <li>{{ $issue['title'] }}</li>
            @endforeach
        </ul>
    </div>
@endif
