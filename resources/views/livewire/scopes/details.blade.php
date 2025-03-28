<div x-data="{ edit: $wire.entangle('showEdit').live }">
    <div class="col-span-2 border rounded-sm border-cds-gray-200 p-4 min-h-16">
        @can('update', $scope)
            <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit scope" />
            <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing scope" />

        @endcan

        <div x-show="!edit">
            @if ($scope->url)
                <flux:subheading class="items-center">
                    <a href="{{ $scope->url }}" target="_blank">{{ $scope->url }}</a>
                    <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
                </flux:subheading>
            @endif
            <flux:subheading class="text-xs">
                <flux:icon.calendar class="inline -mt-0.5" variant="micro" />Created {{ $scope->created_at->toFormattedDateString() }}
            </flux:subheading>

            @if($scope->notes)
                <hr class="mt-2">

                <div>
                    {!! $scope->notes !!}
                </div>
            @endif
        </div>

        <div x-show="edit" x-cloak>
            <livewire:scopes.update-scope :$scope />
        </div>
    </div>
</div>
