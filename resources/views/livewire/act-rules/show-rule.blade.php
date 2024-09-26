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
            <p>{!! $rule->getCriteria()->map(fn ($c) => '<a href="'.route('criteria.show', $c).'">'.$c->getLongName().'</a>')->join(', ') !!}</p>
        </aside>

        {!! Str::markdown($rule->markdown) !!}

        <h2>AI Prompt</h2>
        <aside class="panel">
<pre>Create a PHP class in the namespace "App\Services\AccessibilityContentParser\ActRules\Rules"
that extends "App\Services\AccessibilityContentParser\ActRules\ActRuleBase" and is called "{{ $rule->getRuleRunnerName() }}" that has a function with the signature "protected function findApplicableElements(Crawler $crawler): Crawler" for the following web accessibility rule:

# {{ $rule->name }}

@php
echo \Symfony\Component\Yaml\Yaml::dump([
    'metadata' => $rule->metadata,
])
@endphp

{!! $rule->markdown !!}
</pre>

        </aside>
    </div>
</div>
