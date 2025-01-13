<div>
    <h1>All Siteimprove Accessibility Check IDs</h1>

    <p>Source: <a href="https://help.siteimprove.com/support/solutions/articles/80000448497">Siteimprove Help Center</a></p>

    <table class="table bordered">
        <thead>
            <tr>
                <th style="width: 100px;">Rule Id</th>
                <th>WCAG Criteria</th>
                <th>Issues</th>
                <th>Guidelines</th>
            </tr>
        </thead>
        <tbody>
        @foreach($this->getRuleCategories() as $rule)
            <tr>
                <th>
                    {{ $rule->rule_id }}
                </th>
                <td>
                    {{ $rule->category }}

                </td>
                <td>
                    {{ $rule->issues }}
                </td>
                <td>
                    @foreach($rule->guidelines as $guideline)
                        <x-forms.button size="xs" :href="route('guidelines.show', $guideline)">{{ $guideline->number }}</x-forms.button>
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
