<div>
    <div class="mb-8 w-full max-[992px]:overflow-x-auto">
    <table class="table striped bordered min-w-[900px]">
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
                    <a href="{{ route('scope.show', $scope) }}">{{ $scope->title }}</a>
                </td>
                <td class="max-w-md">
                    <div class="items-center break-words">
                        @if($scope->url)
                            <a href="{{ $scope->url }}" target="_blank">{{ $scope->shortUrl() }}</a>
                            <flux:icon.arrow-top-right-on-square class="inline-block -mt-1 text-zinc-500" variant="micro" />
                        @endif
                    </div>
                </td>
                <td>
                    {!! Str::of($scope->notes)->markdown() !!}
                </td>
                <td>
                    @foreach($scope->issues->sortBy('guideline_id') as $issue)
                        <x-forms.button
                            data-cds-button-assessment
                            class="{{ Str::of(($issue->assessment ?? App\Enums\Assessment::Not_Applicable)->value())->lower()->replace('/', '') }}"
                            size="xs"
                            href="{{ route('issue.show', $issue) }}"
                            title="View issue {{ $issue->guideline ? $issue->guideline->getNumber().\App\Models\Issue::INSTANCE_DIVIDER.$issue->guideline_instance : $issue->id }}"
                        >
                            @if($issue->guideline)
                                {{ $issue->guideline->getNumber().\App\Models\Issue::INSTANCE_DIVIDER.$issue->guideline_instance }}
                            @else
                                N/A
                            @endif
                        </x-forms.button>
                    @endforeach
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
    </div>

    @can('create', [\App\Models\Scope::class, $project])
        <flux:modal.trigger name="add-scope">
            <x-forms.button icon="plus-circle">
                Add Scope
            </x-forms.button>
        </flux:modal.trigger>
        <livewire:scopes.add-scope :project="$project" />
    @endcan
</div>
