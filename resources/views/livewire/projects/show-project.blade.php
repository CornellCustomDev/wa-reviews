<div>
    <h1>{{ $project->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="col-span-3 mb-4">
            <div class="border rounded-sm border-cds-gray-200 p-4" x-data="{ edit: $wire.entangle('showEdit').live }">
                @can('update', $project)
                    <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit project" />
                    <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing project" />
                    <livewire:projects.upload-project-data :project="$project"/>
                @endcan

                <div x-show="!edit">
                    <flux:subheading class="items-center">
                        <a href="{{ $project->site_url }}" target="_blank">{{ $project->site_url }}</a>
                        <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
                    </flux:subheading>
                    <flux:subheading class="text-xs">
                        <flux:icon.calendar class="inline -mt-0.5" variant="micro" />Created {{ $project->created_at->toFormattedDateString() }}
                    </flux:subheading>

                    <hr class="mt-2">

                    @if($project->description)
                        <div>
                            {!! $project->description !!}
                        </div>
                    @endif

                    @if($project->responsible_unit)
                        <div>
                            {{ $project->responsible_unit }}
                        </div>
                    @endif

                    @if($project->contact_name)
                        <div>Point of contact:
                            {{ $project->contact_name }}
                            @if($project->contact_netid)
                                ({{ $project->contact_netid }})
                            @endif
                        </div>
                    @endif

                </div>

                <div x-show="edit" x-cloak>
                    <livewire:projects.update-project :$project />
                </div>
            </div>
        </div>

        <div class="col-span-1 mb-4">
            <div class="border rounded-sm border-cds-gray-200 p-4">
                <livewire:projects.workflow :project="$project"/>
            </div>
        </div>
    </div>

    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="issues">Issues ({{ $project->issues()->count() }})</flux:tab>
            <flux:tab name="scope">Scope ({{ $project->scopes()->count() }})</flux:tab>
            <flux:tab name="siteimprove">
                Siteimprove ({{ count($this->siteimprovePagesWithIssues) }})
            </flux:tab>
            <flux:tab name="report">Report</flux:tab>
            @can('manage-projects', $project->team)
                <flux:tab name="log">Activity</flux:tab>
            @endcan
        </flux:tabs>

        <flux:tab.panel name="issues">
            <livewire:projects.issues :$project />
        </flux:tab.panel>
        <flux:tab.panel name="scope">
            <livewire:scopes.view-scopes :$project />
        </flux:tab.panel>
        <flux:tab.panel name="siteimprove">
            <livewire:projects.siteimprove-pages :$project :siteimprove-pages="$this->siteimprovePagesWithIssues" />
        </flux:tab.panel>
        <flux:tab.panel name="report">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                <div class="col-span-3">
                    <livewire:projects.report-data :$project />
                </div>
                <div class="col-span-2">
                    @can('update-report-viewers', $project)
                        <livewire:projects.report-viewers :$project />
                    @endcan
                </div>
            </div>
        </flux:tab.panel>
        @can('manage-projects', $project->team)
            <flux:tab.panel name="log">
                <livewire:projects.activity-log :$project />
            </flux:tab.panel>
        @endcan
    </flux:tab.group>

</div>

