<div>
    <h1>All ACT Rules</h1>
    <p>Source: https://www.w3.org/WAI/standards-guidelines/act/rules/</p>

    <ul>
        @foreach ($rules as $rule)
            <li>
                <a href="{{ route('act-rules.show', $rule) }}">{{ $rule->name }}</a> ({!! $rule->getCriteria()->map(fn ($c) => '<a href="'.route('criteria.show', $c).'">'.$c->number.'</a>')->join(', ') !!})
            </li>
        @endforeach
    </ul>
</div>
