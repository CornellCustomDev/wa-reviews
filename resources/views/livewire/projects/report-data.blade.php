<div>
    <div class="mb-4 max-w-(--breakpoint-md)" x-data="{ editReport: $wire.entangle('showEditReport').live }">
        <div class="col-span-2 border rounded-sm border-cds-gray-200 p-4">
            @can('update', $project)
                <x-forms.button icon="pencil-square" class="float-right" x-show="!editReport" x-on:click="editReport = !editReport" title="Edit report" />
                <x-forms.button icon="x-mark" x-cloak class="float-right" variant="cds-secondary" x-show="editReport" x-on:click="editReport = !editReport" title="Cancel editing project" />
            @endcan

            <flux:heading level="2" size="xl">Report Data</flux:heading>

            <div x-show="!editReport">
                @if($project->description)
                    <div>
                        {!! $project->description !!}
                    </div>
                @endif
            </div>

            <div x-show="editReport" x-cloak>
                <livewire:projects.update-report :$project />
            </div>
        </div>
    </div>

    <x-forms.button :href="route('project.report', $project)">View Report</x-forms.button>
</div>
