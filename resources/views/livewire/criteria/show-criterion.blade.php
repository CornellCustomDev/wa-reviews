<div>
    <div class="cwd-component">
        <div class="align-right">
            <x-forms.button :href="route('criteria.index')">All Criteria</x-forms.button>
        </div>

        <div class="metadata-set metadata-blocks accent-red-dark">
            <h1>
                {{ $criterion->number }} {{ $criterion->name }}
            </h1>
        </div>
    </div>

    <table class="table bordered">
        <tr>
            <th style="width: 120px">WGAC 2 level</th>
            <td>{{ $criterion->level }}</td>
        </tr>
    </table>

    <h2>Related Guidelines</h2>

    <table class="table bordered">
        <thead>
        <tr>
            <th style="width: 100px">Number</th>
            <th>Guideline</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->guidelines as $guideline)
            <tr>
                <th>
                    <x-forms.button :href="route('guidelines.show', $guideline)">{{ $guideline->number }}</x-forms.button>
                </th>
                <td>
                    {{ $guideline->name }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <h2>Siteimprove Rules</h2>

    <table class="table bordered">
        <thead>
        <tr>
            <th style="width: 100px">Rule Id</th>
            <th>Issues</th>
        </tr>
        </thead>
        <tbody>
        @foreach($this->siteimproveRules() as $rule)
            <tr>
                <th>
                    {{ $rule->rule_id }}
                </th>
                <td>
                    {{ $rule->issues }}
                    {{--                        <a href="{{ route('siteimprove-check-rules.show', $rule) }}">{{ $rule->issues }}</a>--}}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
