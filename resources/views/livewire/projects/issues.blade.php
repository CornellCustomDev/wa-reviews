<div>
    <table class="table striped bordered">
        <thead>
        <tr>
            <td></td>
            <th>Scope</th>
            <th>Target</th>
            <th>Issue</th>
            <th>Assessment</th>
            @if($project->isCompleted())
                <th>Remediation</th>
            @endif
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($this->issues() as $issue)
            <tr wire:key="{{ $issue->id }}">
                <td class="text-nowrap">
                    <a href="{{ route('issue.show', $issue) }}" title="View issue {{ $issue->id }}">
                        @if($issue->guideline)
                            {{ $issue->guideline->number.\App\Models\Issue::INSTANCE_DIVIDER.$issue->guideline_instance }}
                        @else
                            N/A
                        @endif
                    </a>
                </td>
                <td>
                    @if($issue->scope)
                        <a href="{{ route('scope.show', $issue->scope) }}" title="View scope {{ $issue->scope->id }}">
                            {{ $issue->scope->title }}
                        </a>
                    @endif
                </td>
                <td>
                    {{ $issue->target }}
                </td>
                <td>
                    {!! $issue->description !!}
                </td>
                <td>
                    @include('livewire.issues.assessment', ['issue' => $issue])
                </td>
                @if($project->isCompleted())
                    <td class="text-nowrap">
                        {!! $issue->status?->description() !!}
                        @if($issue->needs_mitigation)
                            <div>
                                <flux:icon.exclamation-triangle variant="mini" class="text-yellow-500" />
                                Required
                            </div>
                        @endif
                    </td>
                @endif
                <td class="text-nowrap">
                    <x-forms.button.view
                        :href="route('issue.show', $issue)" title="View Issue {{ $issue->id }}" size="xs"
                    />
                    @can('delete', $issue)
                        <x-forms.button.delete
                            title="Delete Issue {{ $issue->id }}"
                            size="xs"
                            wire:click.prevent="deleteIssue('{{ $issue->id }}')"
                            wire:confirm="Are you sure you want to delete the issue for &quot;{{ $issue->target }}&quot;?"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
    </table>
    <livewire:issues.item-show-guideline />

    @can('create', [\App\Models\Issue::class, $project])
        <x-forms.button.add :href="route('project.issue.create', $project)">Add Issue</x-forms.button.add>
    @endcan
</div>
