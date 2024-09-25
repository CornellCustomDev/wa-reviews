<div>
    <h1>AI Page Analyzer</h1>

    <form wire:submit.prevent="analyze">
        <div>
            <label for="pageUrl">Page URL:</label>
            <input type="text" id="pageUrl" wire:model="pageUrl">
        </div>
        <button type="submit">Analyze</button>
        <label>
            <input type="checkbox" id="entirePage" wire:model="entirePage" />
            entire page
        </label>
        <span wire:loading.delay wire:target="analyze">Processing...</span>

    </form>

    @if ($pageUrl)
        <hr>

        <h2>Sections</h2>
        <ul>
            @foreach ($sections as $section)
                <li>
                    {{ $section['name'] }}: {{ $section['css_selector'] }}
                </li>
            @endforeach
        </ul>

        <h2>Applicable Rules</h2>
        @if ($selectedSection)
            <h3>Selected Filter: {{ $selectedSection['name'] }}: {{ $selectedSection['css_selector'] }}</h3>
        @endif
        @if (count($rules) == 0)
            <p>No rules apply for the selected filter.</p>
        @else
        <ul>
            @foreach ($rules as $rule)
                <li>
                    <a href="{{ route('rules.show', $rule->id) }}">{{ $rule->name }}</a>
                    @if (isset($ruleNodes[$rule->machineName]))
                        | <button wire:click="reviewElementsWithAI('{{ $rule->id }}', '{{ $ruleNodes[$rule->machineName]['cssSelectors'] }}')" class="button">Review with AI</button>  <span wire:loading.delay wire:target="reviewElementsWithAI">Processing...</span>
                        <ul>
                            @foreach ($ruleNodes[$rule->machineName]['nodes'] as $node)
                                <li>
                                    {{ $node['css_selector'] }}: {{ $node['description'] }} (~line {{ $node['line_number'] }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if (isset($candidateIssues[$rule->id]))
                        <ul>
                            @foreach ($candidateIssues[$rule->id] as $issue)
                                <li>
                                    {{ $issue->target }}: {{ $issue->description }}
                                    | <button wire:click="createIssue('{{ $rule->id }}', '{{ $issue->target }}')" class="button">Create Issue</button>  <span wire:loading.delay wire:target="createIssue">Processing...</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
        @endif

        @if ($issuesResponse)
            <div class="panel accent-gold fill">
                <h3>Issues Response</h3>
                {!! $issuesResponse !!}
            </div>
        @endif

{{--        <h2>Page</h2>--}}
{{--        <div class="panel accent-blue-green">--}}
{{--            {!! $pageContent !!}--}}
{{--        </div>--}}

        @if ($prompt)
            <h2>Prompt</h2>
            <div class="panel accent-blue-green">
                <pre>{!! $prompt !!}</pre>
            </div>
        @endif
    @endif
</div>
