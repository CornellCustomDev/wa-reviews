<div>
    @can('update-status', $issue)
        <flux:modal.trigger name="update-status" class="cursor-pointer">
            <flux:card size="sm" class="float-right bg-cds-gray-100! px-2 py-2 ml-3 mb-2">
                <x-forms.field-display label="Status" variation="inline" class="mb-0!">
                    {{ $issue->status ?? 'Reviewed' }}
                </x-forms.field-display>
                @if($issue->needs_mitigation)
                    <div>
                        <flux:icon.exclamation-triangle variant="mini" class="text-yellow-500" />
                        Requires Barrier Mitigation
                    </div>
                @endif
            </flux:card>
        </flux:modal.trigger>
    @else
        <flux:card size="sm" class="float-right bg-cds-gray-100! px-2 py-2 ml-3 mb-2">
            <x-forms.field-display label="Status" variation="inline" class="mb-0!">
                {{ $issue->status ?? 'Reviewed' }}
            </x-forms.field-display>
            @if($issue->needs_mitigation)
                <div>
                    <flux:icon.exclamation-triangle variant="mini" class="text-yellow-500" />
                    Requires Barrier Mitigation
                </div>
            @endif
        </flux:card>
    @endcan


    <flux:modal name="update-status" wire:close="closeUpdateStatus()" class="md:w-96">
        <form class="mb-0!">
            <h3>Update Status</h3>

            <x-forms.select
                label="Status"
                wire:model="status"
                :options="\App\Enums\IssueStatus::toSelectArray()"
                required
            />

            @can('update-needs-mitigation', $issue)
                <x-forms.checkbox
                    label="Requires Barrier Mitigation"
                    wire:model="needs_mitigation"
                />
            @endcan

            <x-forms.button wire:click="updateStatus">Update</x-forms.button>
        </form>
    </flux:modal>
</div>
