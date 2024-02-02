<x-cd.layout.app title="WA Reviews" subtitle="Project">
    <h1>{{ $project->name }}</h1>

    <p>Site: <a href="{{ $project->site_url }}" target="_blank">{{ $project->site_url }}</a></p>
    <p>Created: {{ $project->created_at->toFormattedDateString() }}</p>

    <div>
        {!! $project->description !!}
    </div>
</x-cd.layout.app>
