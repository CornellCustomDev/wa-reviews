<div>
    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Project</th>
            <th>Site</th>
            <th>Reviewer</th>
            <th style="width: 150px">Created</th>
            <th style="width: 100px;">Status</th>
            <th style="width: 100px;">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->projects as $project)
            <tr wire:key="{{ $project->id }}">
                <td><a href="{{ route('project.show', $project) }}">{{ $project->name }}</a></td>
                <td><a href="{{ $project->site_url }}" target="_blank">{{ Str::limit($project->site_url, 40) }}</td>
                <td>
                    @if($project->reviewer)
                        {{ $project->reviewer->name }}
                    @else
                        Not assigned
                    @endif
                </td>
                <td>{{ $project->created_at->toFormattedDateString() }}</td>
                <td>{{ $project->status }}</td>
                <td>
                    <x-forms.button.view
                        title="View project {{ $project->id }}"
                        size="xs"
                        :href="route('project.show', $project)"
                    />
                    @can('delete', $project)
                        <x-forms.button.delete
                            title="Delete Project {{ $project->id }}"
                            size="xs"
                            wire:click.prevent="delete('{{ $project->id }}')"
                            wire:confirm="Are you sure you want to delete the project &quot;{{ $project->name }}&quot;?"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @can('create-projects', $team)
        <x-forms.button.add :href="route('teams.project.create', ['team' => $team->id])">Create New Project</x-forms.button.add>
    @endcan
</div>
