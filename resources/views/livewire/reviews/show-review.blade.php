<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('reviews.index', $review->project) }}" title="Back to Reviews" />
        @can('update', $review)
            <x-forms.link-button route="{{ route('reviews.edit', [$review->project, $review]) }}" title="Edit Review" />
        @endcan
    </div>

    <h1>{{ $review->project->name }}: Review</h1>

    <table class="table bordered">
        <tr>
            <th>Target</th>
            <td>{{ $review->target }}</td>
        </tr>
        <tr>
            <th>Description</th>
            <td>{{ $review->description }}</td>
        </tr>
        <tr>
            <th>Recommendation</th>
            <td>{{ $review->recommendation }}</td>
        </tr>
    </table>
</div>
