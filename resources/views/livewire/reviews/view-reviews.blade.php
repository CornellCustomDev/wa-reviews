<div>
    <div class="cwd-component align-right">
        @can('create', [App\Models\Review::class, $project])
            <x-forms.link-button route="{{ route('reviews.create', $project) }}" title="Create Review" />
        @endcan
        <x-forms.link-button route="{{ route('projects.show', $project) }}" title="Back to Project" />
    </div>

    <h1>{{ $project->name }}: Reviews</h1>

    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Target</th>
            <th>Description</th>
            <th>Recommendation</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reviews as $review)
            <tr wire:key="{{ $review->id }}">
                <td>
                    <livewire:reviews.review-field :key="$review->id . '-target'" :$review field="target" label="Target" />
                </td>
                <td>
                    <livewire:reviews.review-field :key="$review->id . '-description'" :$review field="description" label="Description" />
                </td>
                <td>
                    <livewire:reviews.review-field :key="$review->id . '-recommendation'" :$review field="recommendation" label="Recommendation" />
                </td>
                <td class="text-nowrap">
                    <x-forms.link-button route="{{ route('reviews.show', [$review->project, $review]) }}" title="View Review {{ $review->id }}">
                        <span class="zmdi zmdi-eye" style="margin-right: 0" />
                    </x-forms.link-button>
                    @can('delete', $review)
                        <x-forms.link-button
                            route="#" title="Delete Review {{ $review->id }}"
                            wire:click.prevent="$parent.delete('{{ $review->id }}')"
                            wire:confirm="Are you sure you want to delete the review for &quot;{{ $review->target }}&quot;?"
                        >
                            <span class="zmdi zmdi-delete" style="margin-right: 0" />
                        </x-forms.link-button>
                    @endcan
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
