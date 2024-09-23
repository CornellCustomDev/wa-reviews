<div>
    <div class="cwd-component">
        <div class="align-right">
            <x-forms.link-button route="{{ route('rules.index') }}" title="All ACT Rules" />
        </div>

        <h1>
            {{ $rule->name }}
        </h1>

        <aside class="panel">
            <header>Rule Mapping</header>
            <p>{{ $rule->getMapping() }}</p>
            <p>{!! $rule->getCriteria()->map(fn ($c) => '<a href="'.route('criteria.show', $c).'">'.$c->getLongName().'</a>')->join(', ') !!}</p>
        </aside>

        <h2>Description</h2>
        {!! Str::of($rule->getDescription())->markdown() !!}

        <h2>Applicability</h2>
        {!! Str::of($rule->applicability)->markdown() !!}

        <h2>Expectations</h2>
        {!! Str::of($rule->expectation)->markdown() !!}

        <h2>Assumptions</h2>
        {!! Str::of($rule->assumptions)->markdown() !!}

        <h2>Accessibility Support</h2>
        {!! Str::of($rule->accessibility_support)->markdown() !!}

        <h2>Background</h2>
        {!! Str::of($rule->background)->markdown() !!}

        <h2>Test Cases</h2>
        {!! Str::of($rule->test_cases)->markdown() !!}

        <h2>References</h2>
        <pre>{{ $rule->references }}</pre>
    </div>
</div>
