<div>
    <h1>{{ $project->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="col-span-3 mb-4" x-data="{ edit: $wire.entangle('showEdit').live }">
            <div class="border rounded-sm border-cds-gray-200 p-4">
                @can('update', $project)
                    <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit project" />
                    <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing project" />
                @endcan

                <div x-show="!edit">
                    <flux:subheading class="items-center">
                        <a href="{{ $project->site_url }}" target="_blank">{{ $project->site_url }}</a>
                        <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
                    </flux:subheading>
                    <flux:subheading class="text-xs">
                        <flux:icon.calendar class="inline -mt-0.5" variant="micro" />Created {{ $project->created_at->toFormattedDateString() }}
                    </flux:subheading>

                    @if($project->description)
                        <hr class="mt-2">

                        <div>
                            {!! $project->description !!}
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
                <flux:heading size="lg" class="mb-1">Project Info</flux:heading>
                <flux:separator class="mb-2"/>
                @if($project->reviewer)
                    <x-forms.field-display label="Reviewer" class="mb-0!">
                        {{ $project->reviewer->name }}

                        <div class="float-right">
                            <flux:modal.trigger name="add-assignment">
                                <x-forms.button icon="pencil-square" size="xs" title="Edit reviewer" />
                            </flux:modal.trigger>
                            <x-forms.button.delete
                                title="Remove {{ $project->reviewer->name }} from project"
                                size="xs"
                                wire:click.prevent="removeReviewer"
                                wire:confirm="Are you sure you want to remove &quot;{{ $project->reviewer->name }}&quot; from the project?"
                            />
                        </div>
                    </x-forms.field-display>
                @else
                    @can('update', $project)
                        <flux:modal.trigger name="add-assignment">
                            <x-forms.button icon="plus-circle">Assign Reviewer</x-forms.button>
                        </flux:modal.trigger>
                    @else
                        <x-forms.field-display label="Reviewer" class="mb-0!">
                            <span class="text-gray-500">No reviewer assigned</span>
                        </x-forms.field-display>
                    @endcan
                @endif
                <flux:modal name="add-assignment" wire:close="closeAddAssignment()" class="md:w-96">
                    <livewire:projects.add-assignment :project="$project"/>
                </flux:modal>
            </div>
        </div>
    </div>


    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="issues">Issues ({{ $project->issues()->count() }})</flux:tab>
            <flux:tab name="scope">Scope ({{ $project->scopes()->count() }})</flux:tab>
            <flux:tab name="siteimprove">Siteimprove ({{ count($this->siteimprovePagesWithIssues) }})</flux:tab>
            <flux:tab name="report">Report</flux:tab>
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
            <livewire:projects.report-data :$project />
        </flux:tab.panel>
    </flux:tab.group>

</div>
