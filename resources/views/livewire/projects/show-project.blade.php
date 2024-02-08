<div>
    <div class="cwd-component align-right">
        @can('viewAny', [\App\Models\Review::class, $project])
            <x-forms.link-button route="{{ route('reviews.index', $project) }}" title="View Reviews" />
        @endcan
        <x-forms.link-button route="{{ route('projects.edit', $project) }}" title="Edit Project" />
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
            <th>Created</th>
            <td>{{ $project->created_at->toFormattedDateString() }}</td>
        </tr>
    </table>

    <div>
        {!! $project->description !!}
    </div>
</div>
