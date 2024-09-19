<div class="cwd-component">
    <form>
        <div class="align-right">
            <x-forms.link-button route="{{ route('items.create', [$issue->project, $issue]) }}" title="Add Item" />
        </div>

        <h2>Applicable Guidelines</h2>

        <table class="table striped bordered">
            <thead>
                <tr>
                    <th>Guideline</th>
                    <th>Applicability</th>
                    <th>Recommendations</th>
                    <th>Testing</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    <tr wire:key="{{ $item->id }}">
                        <td>
                            {{ $item->guideline->criterion->getNumberName() }} ({{ $item->assessment }})
                            <hr>
                            {{ $item->guideline->name }} <a href="{{ route('guidelines.show', $item->guideline) }}">View guideline</a>
                        </td>
                        <td>
                            {{ $item->description }}
                        </td>
                        <td>
                            {{ $item->recommendation }}
                        </td>
                        <td>
                            {{ $item->testing }}
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
