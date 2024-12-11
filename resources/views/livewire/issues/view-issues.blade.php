<div>
    <table class="table striped bordered">
        <thead>
            <tr>
                <th>Target</th>
                <th>Description</th>
                <th>Observations</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($scope->issues as $issue)
            <tr wire:key="{{ $issue->id }}">
                <td>
                    {{ $issue->target }}
{{--                    <livewire:issues.issue-field :key="$issue->id.'-target'" :$issue field="target" label="Target"/>--}}
                </td>
                <td>
                    {{ $issue->description }}
{{--                    <livewire:issues.issue-field :key="$issue->id.'-description'" :$issue field="description" label="Description"/>--}}
                </td>
                <td>
                    @if($issue->items)
                        @foreach($issue->items as $item)
                            @include('livewire.issues.item-observation', ['item' => $item])
                        @endforeach
                    @endif
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

    @can('update', $scope->project)
        <x-forms.button.add :href="route('scope.issue.create', $scope)" icon="plus-circle">Add Issue</x-forms.button.add>
    @endcan
</div>
