@php
    $bold = 'font-weight: bold;';
    $textMedium = 'font-size: 12px;';
    $textLarge = 'font-size: 14px;';
    $textXLarge = 'font-size: 16px;';
    $heading = "$textLarge $bold";
    $backgroundLight = 'background-color: #f0f0f0;';
    $backgroundDark = 'background-color: #d9d9d9;';
    $border = 'border: 1px solid #000;';
    $cellHeading = "$heading $backgroundDark $border";
    $wrap = 'word-wrap: break-word;';
    $columns = '16';
@endphp
<table style="font-family: Calibri, Arial, sans-serif; font-size: 11px;">
    <tr>
        <td colspan="{{ $columns }}" style="{{ $textXLarge }} {{ $bold }}">Web Accessibility Assessment Review</td>
    </tr>
    <tr>
        <td colspan="{{ $columns }}" style="{{ $textLarge }}">{{ $project->name }}</td>
    </tr>
    <tr>
        <td colspan="4" style="font-style: italic">
            Prepared by: {{ $project->reviewer->name }} ({{ $project->reviewer->email }})
        </td>
    </tr>
    <tr>
        <td colspan="4" style="font-style: italic">
            Date review completed: {{ $project->completed_at?->format('F j, Y') }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td colspan="4" style="{{ $bold }} {{ $textMedium }}">
            Site URL: {{ $project->site_url }}
        </td>
    </tr>
    <tr></tr>
    <tr>
        <td colspan="{{ $columns }}" style="{{ $textXLarge }} {{ $bold }}">List of Issues Found</td>
    </tr>
    <tr>
        {{-- Widths are supported in Laravel Excel exports --}}
        <td style="{{ $cellHeading }} width:75px;" width="75px">ID</td>
        <td style="{{ $cellHeading }} width:200px;" width="200px">Criterion</td>
        <td style="{{ $cellHeading }} width:500px; {{ $wrap }}" width="500px">Description</td>
        <td style="{{ $cellHeading }} width:50px;">Pass</td>
        <td style="{{ $cellHeading }} width:50px;">Warn</td>
        <td style="{{ $cellHeading }} width:50px;">Fail</td>
        <td style="{{ $cellHeading }} width:50px;">N/A</td>
        <td style="{{ $cellHeading }} width:100px;" width="100px">Impact</td>
        <td style="{{ $cellHeading }}">Scope</td>
        <td style="{{ $cellHeading }} width:350px; {{ $wrap }}" width="350px">Location</td>
        <td style="{{ $cellHeading }} width:500px; {{ $wrap }}" width="500px">Observation</td>
        <td style="{{ $cellHeading }} width:500px; {{ $wrap }}" width="500px">Recommendation</td>
        <td style="{{ $cellHeading }} width:350px; {{ $wrap }}" width="350px">Testing</td>
        <td style="{{ $cellHeading }} width:350px;" width="350px">Images</td>
        <td style="{{ $cellHeading }} width:50px;">CE Issue</td>
        <td style="{{ $cellHeading }} width:300px;" width="300px">Barrier Mitigation Required</td>
    </tr>
{{--    @foreach($issuesByScope as $scope => $issues)--}}
{{--        @php($scope = $issues[0]->scope)--}}
{{--        <tr style="{{ $backgroundLight }}">--}}
{{--            <td colspan="{{ $columns }}" style="{{ $bold }} {{ $backgroundLight }} {{ $textMedium }}">--}}
{{--                @if($scope)--}}
{{--                    {{ $scope->title }} (<a href="{{ $scope->url }}">{{ $scope->url }}</a>)--}}
{{--                @else--}}
{{--                    Issues--}}
{{--                @endif--}}
{{--            </td>--}}
{{--        </tr>--}}
        @foreach($issues as $issue)
            <tr>
                <td>
                    <a href="{{ route('issue.show', $issue) }}">{{ $issue->getGuidelineInstanceNumber() }}</a>
                </td>
                <td>
                    <p>{{ $issue->guideline->criterion->getLongName() }}</p>
                </td>
                <td style="{{ $wrap }}">
                    <p>{{ $issue->guideline->name }}</p>
                </td>
                <td style="background-color: #caff37; text-align: center; border: 1px solid #000;">
                    {{ $issue->assessment == \App\Enums\Assessment::Pass ? 'X' : ' ' }}
                </td>
                <td style="background-color: #f6b26b; text-align: center; border: 1px solid #000;">
                    {{ $issue->assessment == \App\Enums\Assessment::Warn ? 'X' : ' ' }}
                </td>
                <td style="background-color: #ea9999; text-align: center; border: 1px solid #000;">
                    {{ $issue->assessment == \App\Enums\Assessment::Fail ? 'X' : ' ' }}
                </td>
                <td style="background-color: #9fc5e8; text-align: center; border: 1px solid #000;">
                    {{ $issue->assessment == \App\Enums\Assessment::Not_Applicable ? 'X' : ' ' }}
                </td>
                <td>
                    {{ $issue->impact ? $issue->impact->value() : ' ' }}
                </td>
                <td>
                    @if($issue->scope)
                        <b>{{ $issue->scope->title }}</b>
                        @if($issue->scope->url)
                            <br>
                            <a href="{{ $issue->scope->url }}">{{ $issue->scope->url }}</a>
                        @endif
                    @endif
                </td>
                <td style="{{ $wrap }}">
                    {{ $issue->target }}
                </td>
                <td style="{{ $wrap }}">
                    {!! $issue->description !!}
                </td>
                <td style="{{ $wrap }}">
                    {!! $issue->recommendation !!}
                </td>
                <td>
                    @if($issue->testing && !($issue->testing->isEmpty()))
                        {!! $issue->testing !!}
                    @elseif($issue->testing_method)
                        {{ $issue->testing_method->value() }}
                    @endif
                </td>
                <td>
                    @if($issue->image_links)
                        @foreach($issue->image_links as $imagePath)
                            @if($format != 'xlsx')
                                <a href="{{ $imagePath }}">{{ pathinfo($imagePath, PATHINFO_BASENAME) }}</a>
                            @else
                                {{ $imagePath }}
                            @endif
                            @if (!$loop->last)<br>@endif
                        @endforeach
                    @endif
                </td>
                <td>
                    {{ $issue->ce_issue ? 'X' : ' ' }}
                </td>
                <td>
                    {{ $issue->needs_mitigation ? 'X' : ' ' }}
                </td>
            </tr>
        @endforeach
{{--    @endforeach--}}
</table>
