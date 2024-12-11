<div>
    <h1>{{ $project->name }}</h1>

    <div class="mb-4 max-w-screen-md">
        <div class="col-span-2 border rounded border-cds-gray-200 p-4">
            <x-forms.button.edit class="float-right" :href="route('project.edit', $project)" title="Edit Project" />

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
    </div>


    <flux:tab.group>
        <flux:tabs wire:model="tab">
            <flux:tab name="scope">Scope ({{ count($project->issues) }})</flux:tab>
            <flux:tab name="siteimprove">Siteimprove ({{ count($this->siteimprovePagesWithIssues) }})</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="scope" class="!pt-6">
            <livewire:scopes.view-scopes :$project />
        </flux:tab.panel>
        <flux:tab.panel name="siteimprove" class="!pt-6">
            <livewire:projects.siteimprove-pages :$project :siteimprove-pages="$this->siteimprovePagesWithIssues" />
        </flux:tab.panel>
    </flux:tab.group>

</div>
