<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('project.edit', $project) }}" title="Edit" />
    </div>

    <h1>{{ $project->name }}</h1>

    <table class="table bordered">
        <tr>
            <th>Project</th>
            <td>{{ $project->name }}</td>
        </tr>
        <tr>
            <th>Site</th>
            <td><a href="{{ $project->site_url }}" target="_blank">{{ $project->site_url }}</a></td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{!! $project->description !!}</td>
        </tr>
        <tr>
            <th>Siteimprove Report</th>
            <td>
                @if ($project->siteimprove_url)
                    <a href="{{ $project->siteimprove_url }}" target="_blank">View Report</a>
                @else
                    No report available
                @endif
            </td>
        </tr>
        <tr>
            <th>Created</th>
            <td>{{ $project->created_at->toFormattedDateString() }}</td>
        </tr>
    </table>

    <livewire:scopes.view-scopes :$project />

</div>
