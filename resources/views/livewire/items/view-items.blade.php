<div class="cwd-component">
    <form>
        <div class="align-right">
            <x-forms.link-button route="{{ route('items.create', [$issue->project, $issue]) }}" title="Add Item" />
        </div>

        <h1>Observations</h1>

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
                @foreach($items as $item)
                    <tr wire:key="{{ $item->id }}">
                        <td>
                            <livewire:items.item-field :key="$item->id . '-description'" :$item field="description" label="Description" />
                        </td>
                        <td>
                            <a href="{{ route('criteria.show', $item->guideline->criterion) }}">{{ $item->guideline->criterion->getNumberName() }}</a>
                        </td>
                        <td>
                            <livewire:items.item-field :key="$item->id . '-assessment'" :$item field="assessment" label="Assessment" field-type="select" />
                        </td>
                        <td>
                            <livewire:items.item-field :key="$item->id . '-testing_method'" :$item field="testing_method" label="Test Method" field-type="select"/>
                        </td>
                        <td>
                            <livewire:items.item-field :key="$item->id . '-recommendation'" :$item field="recommendation" label="Recommendation" />
                        </td>
                        <td>
                            <livewire:items.item-field :key="$item->id . '-image_links'" :$item field="image_links" label="Image Links" />
                        </td>
                        <td>
                            <livewire:items.item-field-checkbox :key="$item->id . '-content_issue'" :$item field="content_issue" label="CE Issue" field-type="checkbox"/>
                        </td>
                        <td class="text-nowrap">
                            <x-forms.link-button route="{{ route('items.edit', [$issue->project, $issue, $item]) }}" title="Edit Item {{ $item->id }}">
                                <span class="zmdi zmdi-edit" style="margin-right: 0" />
                            </x-forms.link-button>
                            @can('delete', $issue)
                                <x-forms.link-button
                                    route="#" title="Delete Item {{ $item->id }}"
                                    wire:click.prevent="delete('{{ $item->id }}')"
                                    wire:confirm="Are you sure you want to delete this item?"
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
