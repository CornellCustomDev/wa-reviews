<div>
    <h2>Assistance</h2>

    <h3>Scope Analysis</h3>
    <div style="margin-bottom: 2em;">
        <p>Analyze the page to find applicable automated guideline and compliance testing rules. Currently there are {{ $this->rulesCount }} AI-assisted rules.</p>

        <livewire:scopes.analyze-scope :$scope />
    </div>
    <div>
        <button wire:click="$dispatch('complete-siteimprove')" class="button">Complete Siteimprove</button>
    </div>

    @if ($guideline)
        <hr>

        <h3 style="font-size: 125%">
            <a href="{{ route('guidelines.show', $guideline) }}">Guideline {{ $guideline->number }}</a>: {{ $guideline->name }}</h3>

        <button wire:click="hideGuideline" style="float:right">Hide</button>

        <h4>Applicable Automated Rules</h4>
        @if($this->scopeGuidelineRules->isEmpty())
            <p>No applicable rules found for this guideline.</p>
        @else
            <table class="table bordered">
                <thead>
                <tr>
                    <th>Rule</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($this->applicableRules as $ruleInfo)
                    @php
                        /** @var \App\Services\AccessibilityAnalyzer\RuleRunner $ruleRunner */
                        $ruleRunner = $ruleInfo['ruleRunner'];
                    @endphp
                    <tr>
                        <td>
                            {{ $ruleRunner->getName() }}
                            @if($ruleRunner->getRuleType() == 'act')
                                (see <a href="{{ route('act-rules.show', $ruleRunner->getRuleId()) }}">ACT Rule</a>)
                            @endif
                            <button wire:click="reviewElements('{{ $ruleRunner->getMachineName() }}', {{ !$this->aiRuleResults()->isEmpty() }})" class="button small">
                                {{ $this->aiRuleResults()->isEmpty() ? 'Review' : 'Re-review' }}
                                {{ $ruleInfo['count'] }} applicable {{ Str::plural('element', $ruleInfo['count']) }} with AI
                            </button>
                            <span wire:loading.delay wire:target="reviewElements">Processing...</span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if ($this->aiRuleResults()->isNotEmpty())
                <table class="table bordered">
                    <thead>
                    <tr>
                        <th>AI Review Results</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        /** @var \App\Models\ScopeRule $scopeRule */
                    @endphp
                    @foreach ($this->aiRuleResults() as $scopeRule)
                        <tr>
                            <td class="accent-{{ $scopeRule->ai_assessment == 'pass' ? 'green' : 'red' }} fill">
                                <strong>Target:</strong> {{ $scopeRule->ai_description }} ({{ ucfirst($scopeRule->ai_assessment) }})<br>
                                <strong>Reasoning:</strong> {{ $scopeRule->ai_reasoning }}
                                @if ($scopeRule->ai_assessment != 'pass')
                                    <br>
                                    <button wire:click="createIssue('{{ $scopeRule->id }}')" class="button small">Create Issue</button>
                                    <span wire:loading.delay wire:target="createIssue">Processing...</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        <h4>Guideline Details</h4>
        <div class="panel accent-blue fill" style="font-size: 90%">
            {!! Str::markdown($guideline->notes) !!}
        </div>
    @endif

    <div x-show="$wire.response != null && $wire.response != ''" x-cloak>
        <hr>
        <div class="panel accent-gold fill">
            <h3>Debugging info</h3>
            <pre>{{ $response }}</pre>
        </div>
    </div>
</div>
