<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('reviews.index', $review->project) }}" title="Back to Reviews" />
    </div>

    <h1>{{ $review->project->name }}: Review</h1>

    <table class="table bordered">
        <tr>
            <th>Target</th>
            <td><livewire:reviews.review-field :$review field="target" label="Target" /></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><livewire:reviews.review-field :$review field="description" label="Description" /></td>
        </tr>
        <tr>
            <th>Recommendation</th>
            <td><livewire:reviews.review-field :$review field="recommendation" label="Recommendation"/></td>
        </tr>
    </table>

    <livewire:review-items.view-items :review="$review" />
</div>
