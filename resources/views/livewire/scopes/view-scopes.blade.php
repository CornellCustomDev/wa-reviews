<div>
    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Title</th>
            <th>URL</th>
            <th>Notes</th>
            <th style="width: 125px">Issues Found</th>
            <th style="width: 100px">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->scopes as $scope)
            <tr wire:key="{{ $scope->id }}">
                <td>
                    {{ $scope->title }}
                </td>
                <td>
                    {{ $scope->url }}
                </td>
                <td>
                    {!! Str::of($scope->notes)->markdown() !!}
                </td>
                <td>
                    {{ $scope->issues()->count() }}
                </td>
                <td class="text-nowrap">
                    <x-forms.button.view
                        size="xs" :href="route('scope.show', $scope)" title="View scope {{ $scope->id }}"
                    />
                    @can('update', $project)
                        <x-forms.button.delete
                            title="Delete Scope {{ $scope->id }}"
                            size="xs"
                            wire:click.prevent="delete('{{ $scope->id }}')"
                            wire:confirm="Are you sure you want to delete the scope titled &quot;{{ $scope->title }}&quot;?"
                        />
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @can('update', $project)
        <x-forms.button.add :href="route('project.scope.create', $project)">Add Scope</x-forms.button.add>
    @endcan
</div>
