<div>
    @if($this->hasUnreviewedAI())
        <div class="panel accent-gold fill">
            <strong>Note:</strong> Some issues have AI-generated recommendations that have not been reviewed yet.
            Please review them before proceeding.
        </div>
    @endif

    <table class="table striped bordered">
        <thead>
        <tr>
            <td></td>
            <th>Target</th>
            <th>Issue</th>
            <th>Assessment</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->getIssues() as $issue)
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
                    {{ $issue->target }}
                </td>
                <td>
                    {!! $issue->description !!}
                </td>
                <td>
                    @include('livewire.issues.assessment', ['issue' => $issue])
                    <div>
                        @if($issue->isAiGenerated() && ! $issue->isAiAccepted())
                            @can('update', $issue)
                                <x-forms.button
                                    title="Accept AI recommendation for issue {{ $issue->id }}"
                                    icon="hand-thumb-up" size="xs"
                                    wire:click="acceptAI('{{ $issue->id }}')"
                                />
                                <x-forms.button
                                    title="Reject AI recommendation for issue {{ $issue->id }}"
                                    icon="hand-thumb-down" size="xs"
                                    wire:click="rejectAI('{{ $issue->id }}')"
                                />
                                <br>
                            @endcan
                        @endif
                    </div>
                </td>
                <td class="text-nowrap">
                    <x-forms.button.view
                        :href="route('issue.show', $issue)" title="View Issue {{ $issue->id }}" size="xs"
                    />
                    @can('delete', $issue)
                        <x-forms.button.delete
                            title="Delete Issue {{ $issue->id }}"
                            size="xs"
                            wire:click.prevent="delete('{{ $issue->id }}')"
                            wire:confirm="Are you sure you want to delete the issue for &quot;{{ $issue->target }}&quot;?"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <livewire:issues.item-show-guideline />

    @can('update', $scope->project)
        <x-forms.button.add :href="route('scope.issue.create', $scope)" icon="plus-circle">Add Issue</x-forms.button.add>
    @endcan
</div>
