<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('project.create') }}" title="Create New Project" />
    </div>

    <h1>Projects</h1>

    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Project</th>
            <th>Site</th>
            <th style="width: 150px">Created</th>
            <th style="width: 100px;">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($projects as $project)
            <tr wire:key="{{ $project->id }}">
                <td><a href="{{ route('project.show', $project) }}">{{ $project->name }}</a></td>
                <td><a href="{{ $project->site_url }}" target="_blank">{{ Str::limit($project->site_url, 40) }}</td>
                <td>{{ $project->created_at->toFormattedDateString() }}</td>
                <td>
                    <x-forms.link-button route="{{ route('project.show', $project) }}" title="View project {{ $project->id }}">
                        <span class="zmdi zmdi-eye" style="margin-right: 0"/>
                    </x-forms.link-button>
                    @can('delete', $project)
                        <x-forms.link-button
                            route="#" title="Delete Project {{ $project->id }}"
                            wire:click.prevent="delete('{{ $project->id }}')"
                            wire:confirm="Are you sure you want to delete the project &quot;{{ $project->name }}&quot;?"
                        >
                            <span class="zmdi zmdi-delete" style="margin-right: 0"/>
                        </x-forms.link-button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
