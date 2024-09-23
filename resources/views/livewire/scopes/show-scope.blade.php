<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('scope.edit', $scope) }}" title="Edit" />
    </div>

    <h1>{{ $scope->project->name }}: {{ $scope->title }}</h1>

    <table class="table bordered">
        <tr>
            <th>Project</th>
            <td>{{ $scope->project->name }}</td>
        </tr>
        <tr>
            <th>Scope</th>
            <td>{{ $scope->title }}</td>
        </tr>
        <tr>
            <th>URL</th>
            <td>
                <a href="{{ $scope->url }}" target="_blank">{{ $scope->url }}</a>
                | <button wire:click="analyze" class="button">Analyze with AI</button>  <span wire:loading.delay>Processing...</span>
            </td>
        </tr>
        <tr>
            <th>Notes</th>
            <td>{!! Str::of($scope->notes)->markdown() !!}</td>
        </tr>
        <tr>
            <th>Created</th>
            <td>{{ $scope->created_at->toFormattedDateString() }}</td>
        </tr>
    </table>

    @if ($suggestedRules)
        <h2>Suggested Rules</h2>
        <ul>
            @foreach ($suggestedRules as $suggestion)
                <li>
                    <a href="{{ route('rules.show', $suggestion['rule']->id) }}">{{ $suggestion['rule']->name }}</a>

                    @if($suggestion['criteria']->isNotEmpty())
                        (
                        @foreach ($suggestion['criteria'] as $criterion)
                            <a href="{{ route('criteria.show', $criterion) }}">{{ $criterion->getLongName() }}</a>
                        @endforeach
                        )
                    @endif

                    | <button wire:click="createIssues('{{ $suggestion['rule']->id }}')" class="button">Review with AI</button>  <span wire:loading.delay>Processing...</span>
                </li>
            @endforeach
        </ul>
    @endif

    <livewire:issues.view-issues :$scope />
</div>
