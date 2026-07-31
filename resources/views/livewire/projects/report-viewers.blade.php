<div class="border rounded-sm border-cds-gray-200 p-4">
    <flux:heading size="lg" class="mb-1">Report Viewers</flux:heading>
    <flux:separator class="mb-2"/>

    <div class="mb-2">
        @if($project->reportViewers()->count() > 0)
            <ul>
            @foreach($project->reportViewers as $viewer)
                <li class="flex">
                    <div class="flex-1">{{ $viewer->name }} (<a href="mailto:{{ $viewer->email }}">{{ $viewer->email }}</a>)</div>
                    @can('update-report-viewers', $project)
                        <div class="flex-none">
                            <x-forms.button.delete
                                title="Remove {{ $viewer->name }} as a report viewer"
                                size="xs"
                                wire:click.prevent="removeReportViewer({{ $viewer->id }})"
                                wire:confirm="Are you sure you want to remove &quot;{{ $viewer->name }}&quot; as a report viewer?"
                            />
                        </div>
                    @endcan
                </li>
            @endforeach
            </ul>
        @endif
        @can('update-report-viewers', $project)
            <flux:modal.trigger name="add-report-viewer">
                <x-forms.button icon="plus-circle">Add Report Viewer</x-forms.button>
            </flux:modal.trigger>
        @endcan
    </div>
    <flux:modal name="add-report-viewer" wire:close="closeAddReportViewer()" class="md:w-96">
        <livewire:projects.add-report-viewer :project="$project"/>
    </flux:modal>
</div>
