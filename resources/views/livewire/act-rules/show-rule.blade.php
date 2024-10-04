<div>
    <div class="cwd-component">
        <div class="align-right">
            <x-forms.link-button route="{{ route('act-rules.index') }}" title="All ACT Rules" />
        </div>

        <h1>
            {{ $rule->name }}
        </h1>

        <aside class="panel">
            <header>Rule Mapping</header>
            <p>{{ $rule->getMapping() }}</p>
            <p>{!! $rule->criteria?->map(fn ($c) => '<a href="'.route('criteria.show', $c).'">'.$c->getLongName().'</a>')->join(', ') !!}</p>
        </aside>
        @if ($rule->guidelines->isNotEmpty())
        <aside class="panel">
            <header>Guidelines</header>
            @foreach($rule->guidelines as $guideline)
                <div>
                    <x-forms.link-button route="{{ route('guidelines.show', $guideline) }}" title="{{ $guideline->number }}" />
                    {{ $guideline->name }}
                    (<a href="{{ route('criteria.show', $guideline->criterion) }}">{{ $guideline->criterion->getLongName() }}</a>)
                </div>
            @endforeach
        </aside>
        @endif

        {!! Str::markdown($rule->markdown) !!}

        <h2>AI Prompt</h2>
        <aside class="panel">
            <button style="float:right" onclick="navigator.clipboard.writeText(document.getElementById('ai-prompt').innerText).then(() => {alert('Prompt copied to clipboard!')})">Copy to Clipboard</button>
            <pre id="ai-prompt">Create a PHP class in the namespace "App\Services\AccessibilityAnalyzer\ActRules"
that extends "App\Services\AccessibilityAnalyzer\ActRuleRunner" and is called "{{ $rule->getRuleRunnerName() }}" that has a function with the signature "protected function findApplicableElements(Crawler $crawler): Crawler". The base class includes the function "protected function isElementIncludedInAccessibilityTree(\DOMNode $element): bool" for determining if an element is included in the accessibility tree.

Web accessibility rule:

# {{ $rule->name }}

@php
echo \Symfony\Component\Yaml\Yaml::dump([
    'metadata' => $rule->metadata,
])
@endphp

{!! e($rule->markdown) !!}
</pre>

        </aside>
    </div>
</div>
