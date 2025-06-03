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
    $wrap = 'word-wrap: normal;';
    $columns = '15';
@endphp
<table>
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
        <td style="{{ $cellHeading }}" width="50px">ID</td>
        <td style="{{ $cellHeading }}" width="200px">Criterion</td>
        <td style="{{ $cellHeading }}" width="500px">Description</td>
        <td style="{{ $cellHeading }}">Pass</td>
        <td style="{{ $cellHeading }}">Warn</td>
        <td style="{{ $cellHeading }}">Fail</td>
        <td style="{{ $cellHeading }}">N/A</td>
        <td style="{{ $cellHeading }}" width="350px">Location</td>
        <td style="{{ $cellHeading }}" width="500px">Observation</td>
        <td style="{{ $cellHeading }}" width="500px">Recommendation</td>
        <td style="{{ $cellHeading }}" width="350px">Testing</td>
        <td style="{{ $cellHeading }}" width="350px">Images</td>
        <td style="{{ $cellHeading }}" width="100px">Impact</td>
        <td style="{{ $cellHeading }}">CE Issue</td>
        <td style="{{ $cellHeading }}" width="300px">Barrier Mitigation Required</td>
    </tr>
    @foreach($issuesByScope as $scope => $issues)
        @php($scope = $issues[0]->scope)
        <tr style="{{ $backgroundLight }}">
            <td colspan="{{ $columns }}" style="{{ $bold }} {{ $backgroundLight }} {{ $textMedium }}">
                @if($scope)
                    {{ $scope->title }} ({{ $scope->url }})
                @else
                    Source: Not Set
                @endif
            </td>
        </tr>
        @foreach($issues as $issue)
            @foreach($issue->items as $item)
                <tr>
                    <td>
                        <a href="{{ route('guidelines.show', $item->guideline) }}">{{ $item->guideline->number }}</a>
                    </td>
                    <td>
                        <p>{{ $item->guideline->criterion->getLongName() }}</p>
                    </td>
                    <td>
                        <p>{{ $item->guideline->name }}</p>
                    </td>
                    <td style="background-color: #caff37; text-align: center; border: 1px solid #000;">
                        {{ $item->assessment == \App\Enums\Assessment::Pass ? 'X' : ' ' }}
                    </td>
                    <td style="background-color: #f6b26b; text-align: center; border: 1px solid #000;">
                        {{ $item->assessment == \App\Enums\Assessment::Warn ? 'X' : ' ' }}
                    </td>
                    <td style="background-color: #ea9999; text-align: center; border: 1px solid #000;">
                        {{ $item->assessment == \App\Enums\Assessment::Fail ? 'X' : ' ' }}
                    </td>
                    <td style="background-color: #9fc5e8; text-align: center; border: 1px solid #000;">
                        {{ $item->assessment == \App\Enums\Assessment::Not_Applicable ? 'X' : ' ' }}
                    </td>
                    <td>
                        {{ $item->issue->target }}
                    </td>
                    <td>
                        {!! $item->description !!}
                    </td>
                    <td>
                        {!! $item->recommendation !!}
                    </td>
                    <td>
                        {!! $item->testing !!}
                    </td>
                    <td>
                        @if($item->image_links)
                            @foreach($item->image_links as $imagePath)
                                @php($imageName = pathinfo($imagePath, PATHINFO_BASENAME))
                                {{ $imageName }}
                            @endforeach
                        @endif
                    </td>
                    <td>
                        {{ $item->impact ? $item->impact->value() : ' ' }}
                    </td>
                    <td>
                        {{ $item->ce_issue ? 'X' : ' ' }}
                    </td>
                    <td>
                        {{ $item->issue->needs_mitigation ? 'X' : ' ' }}
                    </td>
                </tr>
            @endforeach
        @endforeach
    @endforeach
</table>
