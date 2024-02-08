<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('projects.show', $project) }}" title="Back to Project" />
    </div>

    <h1>{{ $project->name }}: Reviews</h1>

    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Target</th>
            <th>Description</th>
            <th>Recommendation</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reviews as $review)
            <tr wire:key="{{ $review->id }}">
                <td>{{ $review->target }}</td>
                <td>{{ $review->description }}</td>
                <td>{{ $review->recommendation }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
