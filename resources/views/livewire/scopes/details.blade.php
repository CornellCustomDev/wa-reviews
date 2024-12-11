<div>
    <div class="col-span-2 border rounded border-cds-gray-200 p-4">
        <x-forms.button.edit class="float-right" :href="route('scope.edit', $scope)" title="Edit Scope" />

        <flux:subheading class="items-center">
            <a href="{{ $scope->url }}" target="_blank">{{ $scope->url }}</a>
            <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
        </flux:subheading>
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
</div>
