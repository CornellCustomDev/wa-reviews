<div>
    <div class="cwd-component align-right">
        @can('update', $project)
            <x-forms.button :href="route('project.scope.create', $project)">Add Scope</x-forms.button>
        @endcan
    </div>

    <h2>Scope</h2>

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
                    <x-forms.button :href="route('scope.show', $scope)" title="View scope {{ $scope->id }}">
                        <span class="zmdi zmdi-eye" style="margin-right: 0"/>
                    </x-forms.button>
                    @can('update', $project)
                        <x-forms.button
                            title="Delete Scope {{ $scope->id }}"
                            wire:click.prevent="delete('{{ $scope->id }}')"
                            wire:confirm="Are you sure you want to delete the scope titled &quot;{{ $scope->title }}&quot;?"
                        >
                            <span class="zmdi zmdi-delete" style="margin-right: 0"/>
                        </x-forms.button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
