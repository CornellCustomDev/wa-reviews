<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('projects.show', $review->project) }}" title="Back to Project" />
    </div>

    <h1>{{ $review->project->name }}: Issue</h1>

    <table class="table bordered">
        <tr>
            <th>Target</th>
            <td><livewire:reviews.review-field :$review field="target" label="Target" /></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><livewire:reviews.review-field :$review field="description" label="Description" /></td>
        </tr>
    </table>

    <livewire:review-items.view-items :review="$review" />
</div>
