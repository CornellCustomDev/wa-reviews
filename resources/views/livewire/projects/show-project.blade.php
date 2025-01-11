<div>
    <h1>{{ $project->name }}</h1>

    <div class="mb-4 max-w-screen-md" x-data="{ edit: $wire.entangle('showEdit').live }">
        <div class="col-span-2 border rounded border-cds-gray-200 p-4">
            @can('update', $project)
                <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit project" />
                <x-forms.button icon="x-mark" x-cloak class="float-right" variant="cds-secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing project" />
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


    <flux:tab.group>
        <flux:tabs wire:model.live="tab">
            <flux:tab name="issues" :accent="false">Issues ({{ $project->issues()->count() }})</flux:tab>
            <flux:tab name="scope" :accent="false">Scope ({{ $project->scopes()->count() }})</flux:tab>
            <flux:tab name="siteimprove" :accent="false">Siteimprove ({{ count($this->siteimprovePagesWithIssues) }})</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="issues" class="!pt-6">
            <livewire:projects.issues :$project />
        </flux:tab.panel>
        <flux:tab.panel name="scope" class="!pt-6">
            <livewire:scopes.view-scopes :$project />
        </flux:tab.panel>
        <flux:tab.panel name="siteimprove" class="!pt-6">
            <livewire:projects.siteimprove-pages :$project :siteimprove-pages="$this->siteimprovePagesWithIssues" />
        </flux:tab.panel>
    </flux:tab.group>

</div>
