<div class="cwd-component">
    <form>
        <div class="align-right">
            <x-forms.link-button route="{{ route('review-items.create', [$review->project, $review]) }}" title="Add Review Item" />
        </div>

        <h1>Review Items</h1>

        <table class="table striped bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Criteria</th>
                    <th>Assessment</th>
                    <th>Observed functionality (Description)</th>
                    <th>Test Method</th>
                    <th>Recommendation for Remediation</th>
                    <th>Image Links</th>
                    <th>CE Issue</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reviewItems as $reviewItem)
                    <tr wire:key="{{ $reviewItem->id }}">
                        <td>
                            <x-forms.link-button route="{{ route('guidelines.show', $reviewItem->guideline) }}" title="{{ $reviewItem->guideline->number }}" />
                        </td>
                        <td>
                            <a href="{{ route('categories.show', $reviewItem->guideline->category) }}">
                                {{ $reviewItem->guideline->category->name }}
                            </a>
                        </td>
                        <td>
                            {{ $reviewItem->guideline->criterion->getLongName() }}
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-assessment'" :$reviewItem field="assessment" label="Assessment" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-description'" :$reviewItem field="description" label="Description" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-testing_method'" :$reviewItem field="testing_method" label="Test Method" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-recommendation'" :$reviewItem field="recommendation" label="Recommendation" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-image_links'" :$reviewItem field="image_links" label="Image Links" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-content_issue'" :$reviewItem field="content_issue" label="CE Issue" />
                        </td>
                        <td class="text-nowrap">
                            <x-forms.link-button route="{{ route('review-items.edit', [$review->project, $review, $reviewItem]) }}" title="Edit Review Item {{ $reviewItem->id }}">
                                <span class="zmdi zmdi-edit" style="margin-right: 0" />
                            </x-forms.link-button>
                            @can('delete', $review)
                                <x-forms.link-button
                                    route="#" title="Delete Review Item {{ $reviewItem->id }}"
                                    wire:click.prevent="delete('{{ $reviewItem->id }}')"
                                    wire:confirm="Are you sure you want to delete this review item?"
                                >
                                    <span class="zmdi zmdi-delete" style="margin-right: 0" />
                                </x-forms.link-button>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </form>
</div>
