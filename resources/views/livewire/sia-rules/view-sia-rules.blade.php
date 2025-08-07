<div>
    <h1>Site Improve Alfa Rules</h1>

    <p>Source: <a href="https://alfa.siteimrove.com/rules">Siteimprove Alfa Hub</a></p>

    <table class="table bordered">
        <thead>
            <tr>
                <th style="width: 100px;">Alfa Id</th>
                <th>Name</th>
                <th>WCAG</th>
{{--                <th>Act Rule</th>--}}
                <th>Criteria</th>
            </tr>
        </thead>
        <tbody>
        @foreach($this->siaRules() as $rule)
            <tr wire:key="$rule->id">
                <th>
                    <a href="{{ route('sia-rules.show', $rule) }}">{{ Str::upper($rule->alfa) }}</a>
                </th>
                <td>
                    {!! $rule->name_html !!}
                </td>
                <td>
                    @foreach($rule->criteria as $criterion)
                        @if($criterion->level === 'AAA')
                            @continue
                        @endif
                        <a href="{{ $criterion->requirements->link }}">{{ $criterion->number }}</a>@if(!$loop->last), @endif
                    @endforeach
                </td>
{{--                <td>--}}
{{--                    @if($rule->act_rule_id)--}}
{{--                        <a href="{{ route('act-rules.show', $rule->act_rule_id) }}">{{ $rule->act_rule_id }}</a>--}}
{{--                    @endif--}}
{{--                </td>--}}
                <td>
                    @if($rule->act_rule_id)
                        @foreach($rule->actRule->criteria as $criterion)
                            <a href="{{ route('criteria.show', $criterion) }}">{{ $criterion->number }}</a>@if(!$loop->last), @endif
                        @endforeach
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
