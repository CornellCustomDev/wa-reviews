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

                    @if(!empty($item->image_links))
                        <div class="flex flex-wrap gap-1 mt-1">
                            @foreach($item->image_links as $imagePath)
                                @php($imageName = pathinfo($imagePath, PATHINFO_BASENAME))
                                <flux:tooltip position="bottom" class="align-middle">
                                    <flux:button wire:click="viewImage('{{ $imagePath }}')" :loading="false" class="px-0.5! w-24 h-24 overflow-hidden hover:border-cds-blue-900">
                                        <div class="relative w-full h-full">
                                            <img
                                                src="{{ $imagePath }}"
                                                alt="Preview of image: {{ $imageName }}"
                                                class="absolute top-0.5 left-0 object-cover object-top rounded-sm"
                                            />
                                        </div>
                                    </flux:button>
                                    <flux:tooltip.content>
                                        View image {{ $imageName }}
                                    </flux:tooltip.content>
                                </flux:tooltip>
                            @endforeach
                        </div>
                    @endif
                </td>
                <td style="vertical-align: top">
                    {!! $item->recommendation !!}
                </td>
                <td style="vertical-align: top">
                    {!! $item->testing !!}
                </td>
                <td class="text-nowrap">
                    @can('update', $issue)
                        <x-forms.button
                            title="Edit Item {{ $item->id }}"
                            icon="pencil-square" size="xs"
                            wire:click="edit('{{ $item->id }}')"
                        />
                    @endcan
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
    <flux:modal name="edit-item" wire:close="closeEdit()" class="md:w-[600px]">
        @if ($editItem)
            <livewire:items.update-item :item="$editItem" />
        @endif
    </flux:modal>
    <flux:modal name="view-image" class="max-w-4xl" wire:close="closeImage()">
        @if ($selectedImage)
            <flux:subheading class="mb-2">{{ basename($selectedImage) }}</flux:subheading>
            <div class="border border-cds-gray-900">
                <img src="{{ $selectedImage }}" alt="Selected Image" class="w-full h-auto">
            </div>
        @endif
    </flux:modal>
</div>
