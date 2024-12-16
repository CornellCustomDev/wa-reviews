<div class="cwd-component">
    <form>
        <div class="align-right">
            <x-forms.button.add :href="route('issue.item.create', $issue)">Add Item</x-forms.button.add>
        </div>

        <h2>Applicable Guidelines</h2>

        <table class="table striped bordered">
            <thead>
                <tr>
                    <th>Guideline</th>
                    <th>Observations</th>
                    <th>Recommendations</th>
                    <th>Testing</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($issue->items as $item)
                    <tr wire:key="{{ $item->id }}">
                        <td>
                            <a href="{{ route('guidelines.show', $item->guideline) }}">Guideline {{ $item->guideline->number }}</a> {{ $item->guideline->name }} ({{ $item->assessment }})
                            <hr>
                            {{ $item->guideline->criterion->getNumberName() }}
                        </td>
                        <td style="vertical-align: top">
                            {{ $item->description }}
                        </td>
                        <td style="vertical-align: top">
                            {{ $item->recommendation }}
                        </td>
                        <td style="vertical-align: top">
                            {{ $item->testing }}
                        </td>
                        <td class="text-nowrap">
                            <x-forms.button.edit
                                :href="route('issue.item.edit', [$issue, $item])"
                                title="Edit Item {{ $item->id }}"
                                size="xs"
                            />
                            @can('delete', $issue)
                                <x-forms.button.delete
                                    title="Delete Item {{ $item->id }}"
                                    size="xs"
                                    wire:click.prevent="delete('{{ $item->id }}')"
                                    wire:confirm="Are you sure you want to delete this item?"
                                />
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </form>
</div>
