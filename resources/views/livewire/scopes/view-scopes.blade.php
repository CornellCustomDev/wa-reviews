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
                        title="View scope {{ $scope->id }}"
                        :href="route('scope.show', $scope)"
                        size="xs"
                    />
                    @can('delete', $scope)
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

    @can('create', [\App\Models\Scope::class, $project])
        <flux:modal.trigger name="add-scope">
            <x-forms.button icon="plus-circle">
                Add Scope
            </x-forms.button>
        </flux:modal.trigger>
        <livewire:scopes.add-scope :project="$project" />
    @endcan
</div>
