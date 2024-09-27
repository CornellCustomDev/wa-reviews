<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('scope.edit', $scope) }}" title="Edit" />
    </div>

    <h1>{{ $scope->project->name }}: {{ $scope->title }}</h1>

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
            <td><a href="{{ $scope->url }}" target="_blank">{{ $scope->url }}</a></td>
        </tr>
        <tr>
            <th>Notes</th>
            <td>{!! $scope->notes !!}</td>
        </tr>
        <tr>
            <th>Created</th>
            <td>{{ $scope->created_at->toFormattedDateString() }}</td>
        </tr>
    </table>

    <livewire:issues.view-issues :$scope />
</div>
