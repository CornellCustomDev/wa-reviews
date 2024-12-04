<div>
    <h1>All Siteimprove Accessibility Check IDs</h1>

    <p>Source: <a href="https://help.siteimprove.com/support/solutions/articles/80000448497">Siteimprove Help Center</a></p>

    <table class="table bordered">
        <thead>
            <tr>
                <th style="width: 100px;">Rule Id</th>
                <th>WCAG Criteria</th>
                <th>Issues</th>
            </tr>
        </thead>
        <tbody>
        @foreach($rules as $rule)
            <tr>
                <th>
                    {{ $rule->rule_id }}
                </th>
                <td>
                    @if($rule->criterion)
                        <a href="{{ route('criteria.show', $rule->criterion) }}">{{ $rule->criterion->getLongName() }}</a>
                    @else
                        {{ $rule->category }}
                    @endif
                </td>
                <td>
                    {{ $rule->issues }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
