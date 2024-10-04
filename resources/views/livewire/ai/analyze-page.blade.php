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

        <h2>Sections Found</h2>
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
                        <a href="{{ route('act-rules.show', $rule->id) }}">{{ $rule->name }}</a>
                        @if (isset($ruleNodes[$rule->getMachineName()]))
                            | <button wire:click="reviewElementsWithAI('{{ $rule->id }}', '{{ $ruleNodes[$rule->getMachineName()]['cssSelectors'] }}')" class="button">Review with AI</button>  <span wire:loading.delay wire:target="reviewElementsWithAI">Processing...</span>
                            <ul>
                                @foreach ($ruleNodes[$rule->getMachineName()]['nodes'] as $node)
                                    <li>
                                        {{ $node['description'] }} (~line {{ $node['line_number'] }})
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

        @if ($prompt)
            <h2>Prompt</h2>
            <div class="panel accent-blue-green">
                <pre>{!! $prompt !!}</pre>
            </div>
        @endif
    @endif
</div>
