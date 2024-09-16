<div class="cwd-component">
    <form>
        <div class="align-right">
            <x-forms.link-button route="{{ route('review-items.create', [$review->project, $review]) }}" title="Add Review Item" />
        </div>

        <h1>Issue Details</h1>

        <table class="table striped bordered">
            <thead>
                <tr>
                    <th>Observation</th>
                    <th>WCAG Criteria</th>
                    <th>Assessment</th>
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
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-description'" :$reviewItem field="description" label="Description" />
                        </td>
                        <td>
                            <a href="{{ route('criteria.show', $reviewItem->guideline->criterion) }}">{{ $reviewItem->guideline->criterion->getNumberName() }}</a>
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-assessment'" :$reviewItem field="assessment" label="Assessment" field-type="select" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-testing_method'" :$reviewItem field="testing_method" label="Test Method" field-type="select"/>
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-recommendation'" :$reviewItem field="recommendation" label="Recommendation" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field :key="$reviewItem->id . '-image_links'" :$reviewItem field="image_links" label="Image Links" />
                        </td>
                        <td>
                            <livewire:review-items.review-item-field-checkbox :key="$reviewItem->id . '-content_issue'" :$reviewItem field="content_issue" label="CE Issue" field-type="checkbox"/>
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
