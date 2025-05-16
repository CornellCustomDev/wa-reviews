<div>
    <div class="mb-4 max-w-(--breakpoint-md)" x-data="{ editReport: $wire.entangle('showEditReport').live }">
        <div class="col-span-2 border rounded-sm border-cds-gray-200 p-4">
            @can('update', $project)
                <x-forms.button icon="pencil-square" class="float-right" x-show="!editReport" x-on:click="editReport = !editReport" title="Edit report" />
                <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="editReport" x-on:click="editReport = !editReport" title="Cancel editing project" />
            @endcan

            <flux:heading level="2" size="xl">Report Data</flux:heading>

            <div x-show="!editReport">
                <div>
                    <x-forms.field-display label="URLs included in review">
                        {!! $project->urls_included ?? '' !!}
                    </x-forms.field-display>

                    <x-forms.field-display label="URLs excluded from review">
                        {!! $project->urls_excluded ?? '' !!}
                    </x-forms.field-display>

                    <x-forms.field-display label="Testing notes and procedure">
                        {!! $project->review_procedure ?? '' !!}
                    </x-forms.field-display>

                    <x-forms.field-display class="mb-0!" label="Summary and Overall Findings">
                        {!! $project->summary ?? '' !!}
                    </x-forms.field-display>
                </div>
            </div>

            <div x-show="editReport" x-cloak>
                <livewire:projects.update-report :$project />
            </div>
        </div>
    </div>

    @if($project->isInProgress() || $project->isCompleted())
        <x-forms.button :href="route('project.report', $project)">
            View {{ $project->isInProgress() ? 'Draft' : '' }} Report
        </x-forms.button>
    @endif
</div>
