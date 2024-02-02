<x-cd.layout.app title="WA Reviews" subtitle="Projects">
    <h1>Projects</h1>

    <table class="table -table-responsive">
        <caption>Projects</caption>
        <thead>
        <tr>
            <th>Project</th>
            <th>Site</th>
            <th>Created</th>
        </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
                <tr>
                    <td><a href="{{ route('projects.show', $project) }}">{{ $project->name }}</a></td>
                    <td><a href="{{ $project->site_url }}" target="_blank">{{ Str::limit($project->site_url, 40) }}</td>
                    <td>{{ $project->created_at->toFormattedDateString() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</x-cd.layout.app>
