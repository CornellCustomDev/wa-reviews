<div>
    <h3>Page Content History</h3>

    <ul>
        @foreach($this->pages as $page)
            <li>
                {{ $page->retrieved_at->toDayDateTimeString() }}
                @if($page->id === $this->scope->current_page_id)
                    <flux:icon.star class="text-yellow-500 inline-flex items-center" />
                @endif
                @can('update', $scope)
                    <x-forms.button
                        wire:click="makeCurrentPage({{ $page }})"
                        icon="eye"
                        title="Make current page"
                        class="inline-flex items-center"
                    />
                    <x-forms.button.delete
                        wire:click="deletePage({{ $page }})"
                        title="Delete this page"
                        class="inline-flex items-center"
                    />
                @endcan
            </li>
        @endforeach
    </ul>

    @can('update', $scope)
        <div class="mb-4">
            <x-forms.button wire:click="retrieveContent()" icon="arrow-down-on-square">
                Retrieve Content
            </x-forms.button>
            <span wire:loading.delay wire:target="retrieveContent"> Retrieving...</span>
        </div>
    @endcan
</div>
