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
                | <button wire:click="analyze" class="button">Analyze</button>
                <label>
                    <input type="checkbox" id="entirePage" wire:model="entirePage" />
                    entire page
                </label>
                <span wire:loading.delay wire:target="analyze">| Processing...</span>
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
                    <a href="{{ route('act-rules.show', $suggestion['rule']->id) }}">{{ $suggestion['rule']->name }}</a>

                    @if($suggestion['criteria']->isNotEmpty())
                        (
                        @foreach ($suggestion['criteria'] as $criterion)
                            <a href="{{ route('criteria.show', $criterion) }}">{{ $criterion->getLongName() }}</a>
                        @endforeach
                        )
                    @endif

                    | <button wire:click="reviewElements('{{ $suggestion['rule']->id }}', '{{ $suggestion['cssSelectors'] }}')" class="button">Review {{ count($suggestion['elements']) }} elements with AI</button>  <span wire:loading.delay wire:target="reviewElements">Processing...</span>
                    @if ($suggestion['results'])
                        <ul>
                            @foreach ($suggestion['results'] as $element)
                                <li>
                                    {{ $element['cssSelector'] }} <br>
                                    <b>{{ ucfirst($element['assessment']) }}:</b> {{ $element['reasoning'] }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <livewire:issues.view-issues :$scope />
</div>

{{-- Sidebar for AI help --}}
{{--<x-slot:sidebarPrimary>--}}
{{--    <livewire:ai.scope-help :$response />--}}
{{--</x-slot:sidebarPrimary>--}}
