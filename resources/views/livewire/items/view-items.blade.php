<div class="cwd-component">
    @can('update', $issue)
        <div class="align-right">
            <x-forms.button.add :href="route('issue.item.create', $issue)" title="Add Item" />
        </div>
    @endcan

    <h2>Applicable Guidelines</h2>

    <table class="table striped bordered">
        <thead>
        <tr>
            <th>Assessment</th>
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
                    @include('livewire.issues.item-observation', ['item' => $item])
                </td>
                <td style="vertical-align: top">
                    {!! $item->description !!}
                </td>
                <td style="vertical-align: top">
                    {!! $item->recommendation !!}
                </td>
                <td style="vertical-align: top">
                    {{ $item->testing }}
                </td>
                <td class="text-nowrap">
                    <x-forms.button
                        title="Edit Item {{ $item->id }}"
                        icon="pencil-square" size="xs"
                        wire:click="edit('{{ $item->id }}')"
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
    <flux:modal name="edit-item" wire:close="closeEdit()" class="max-w-screen-xl">
        @if ($editItem)
            <livewire:items.update-item :item="$editItem" />
        @endif
    </flux:modal>
</div>
