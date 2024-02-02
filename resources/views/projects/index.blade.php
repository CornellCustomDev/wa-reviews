<x-cd.layout.app title="WA Reviews" subtitle="Projects">
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('projects.create') }}" title="Create New Project" />
    </div>

    <h1>Projects</h1>

    <table class="table striped bordered">
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
