@props([
    'projects'
])
<flux:table class="table striped bordered">
    <flux:table.columns>
        <flux:table.column>Project</flux:table.column>
        <flux:table.column>Site</flux:table.column>
        <flux:table.column>Reviewer</flux:table.column>
        <flux:table.column class="w-[150px]">Created</flux:table.column>
        <flux:table.column class="w-[100px]">Status</flux:table.column>
        <flux:table.column class="w-[100px]">Actions</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @foreach ($projects as $project)
            <flux:table.row :key="$project->id">
                <flux:table.cell>
                    <a href="{{ route('project.show', $project) }}">{{ $project->name }}</a>
                </flux:table.cell>
                <flux:table.cell>
                    @if($project->site_url)
                        <a href="{{ $project->site_url }}"
                           target="_blank">{{ Str::limit($project->site_url, 40) }}</a>
                        <flux:icon.arrow-top-right-on-square class="inline-block -mt-1 text-zinc-500"
                                                             variant="micro"/>
                    @endif
                </flux:table.cell>
                <flux:table.cell>
                    @if($project->reviewer)
                        {{ $project->reviewer->name }}
                    @else
                        Not assigned
                    @endif
                    @if($this->showTeams() && $project->team)
                        (<a href="{{ route('teams.show', $project->team) }}">{{ $project->team->name }}</a>)
                    @endif
                </flux:table.cell>
                <flux:table.cell class="whitespace-nowrap">
                    {{ $project->created_at->toFormattedDateString() }}
                </flux:table.cell>
                <flux:table.cell>{{ $project->status }}</flux:table.cell>
                <flux:table.cell>
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
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
