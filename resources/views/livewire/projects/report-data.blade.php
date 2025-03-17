<div>
    <div class="mb-4 max-w-(--breakpoint-md)" x-data="{ editReport: $wire.entangle('showEditReport').live }">
        <div class="col-span-2 border rounded-sm border-cds-gray-200 p-4">
            @can('update', $project)
                <x-forms.button icon="pencil-square" class="float-right" x-show="!editReport" x-on:click="editReport = !editReport" title="Edit report" />
                <x-forms.button icon="x-mark" x-cloak class="float-right" variant="cds-secondary" x-show="editReport" x-on:click="editReport = !editReport" title="Cancel editing project" />
            @endcan

            <flux:heading level="2" size="xl">Report Data</flux:heading>

            <div x-show="!editReport">
                <div>
                    @if($project->responsible_unit)
                        <flux:heading level="3" size="lg">Responsible unit at Cornell</flux:heading>
                        <p>{!! $project->responsible_unit !!}</p>
                    @endif
                    @if($project->contact_name)
                        <flux:heading level="3" size="lg">Point of Contact</flux:heading>
                        <p>{!! $project->contact_name !!} ({!! $project->contact_netid !!})</p>
                    @endif
                    @if($project->audience)
                        <flux:heading level="3" size="lg">Who is the audience?</flux:heading>
                        <p>{!! $project->audience !!}</p>
                    @endif
                    @if($project->site_purpose)
                        <flux:heading level="3" size="lg">What is the purpose of the site?</flux:heading>
                        <p>{!! $project->site_purpose !!}</p>
                    @endif
                    @if($project->urls_included)
                        <flux:heading level="3" size="lg">URLs included in review</flux:heading>
                        <p>{!! $project->urls_included !!}</p>
                    @endif
                    @if($project->urls_excluded)
                        <flux:heading level="3" size="lg">URLs excluded from review</flux:heading>
                        <p>{!! $project->urls_excluded !!}</p>
                    @endif
                    @if($project->review_procedure)
                        <flux:heading level="3" size="lg">Review procedure</flux:heading>
                        <p>{!! $project->review_procedure !!}</p>
                    @endif
                    @if($project->summary)
                        <flux:heading level="3" size="lg">Summary and Overall Findings</flux:heading>
                        <p>{!! $project->summary !!}</p>
                    @endif
                </div>
            </div>

            <div x-show="editReport" x-cloak>
                <livewire:projects.update-report :$project />
            </div>
        </div>
    </div>

    <x-forms.button :href="route('project.report', $project)">View Report</x-forms.button>
</div>
