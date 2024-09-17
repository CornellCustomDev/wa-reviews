<div>
    <div class="cwd-component align-right">
        @can('create', [App\Models\Issue::class, $project])
            <x-forms.link-button route="{{ route('issues.create', $project) }}" title="Add Issue"/>
        @endcan
    </div>

    <h2>Issues</h2>

    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Target</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($issues as $issue)
            <tr wire:key="{{ $issue->id }}">
                <td>
                    <livewire:issues.issue-field :key="$issue->id . '-target'" :$issue field="target"
                                                   label="Target"/>
                </td>
                <td>
                    <livewire:issues.issue-field :key="$issue->id . '-description'" :$issue field="description"
                                                   label="Description"/>
                </td>
                <td class="text-nowrap">
                    <x-forms.link-button route="{{ route('issues.show', [$issue->project, $issue]) }}"
                                         title="View issue {{ $issue->id }}">
                        <span class="zmdi zmdi-eye" style="margin-right: 0"/>
                    </x-forms.link-button>
                    @can('delete', $issue)
                        <x-forms.link-button
                            route="#" title="Delete Issue {{ $issue->id }}"
                            wire:click.prevent="delete('{{ $issue->id }}')"
                            wire:confirm="Are you sure you want to delete the issue for &quot;{{ $issue->target }}&quot;?"
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
