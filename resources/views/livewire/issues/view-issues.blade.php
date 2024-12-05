<div>
    <div class="cwd-component align-right">
        @can('update', $scope->project)
            <x-forms.link-button route="{{ route('scope.issue.create', $scope) }}" title="Add Issue"/>
        @endcan
    </div>

    <h2>Issues</h2>

    <table class="table striped bordered">
        <thead>
            <tr>
                <th>Target</th>
                <th>Description</th>
                <th>Observations</th>
                <th style="width: 100px">Actions</th>
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
                            <x-forms.link-button
                                route="#" title="{{ $item->guideline->number }}"
                                x-data x-on:click.prevent="$dispatch('show-guideline', {number: {{ $item->guideline->number }} })"
                            />
                            ({{ $item->assessment }})
                            @if(!$loop->last)
                                <br>
                            @endif
                        @endforeach
                    @endif
                </td>
                <td class="text-nowrap">
                    <x-forms.link-button route="{{ route('issue.show', $issue) }}"
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
