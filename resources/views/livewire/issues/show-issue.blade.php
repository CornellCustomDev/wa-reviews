<div>
    <div class="cwd-component align-right">
        @if($issue->scope)
            <x-forms.button.back :href="route('scope.show', $issue->scope)" title="Back to Scope" />
        @else
            <x-forms.button.back :href="route('project.show', ['project' => $issue->project, 'tab' => 'issues'])" title="Back to Project" />
        @endif
    </div>

    <h1>{{ $issue->project->name }}: Issue</h1>

    <div class="col-span-2 border rounded-sm border-cds-gray-200 p-4 mb-8 min-h-16" x-data="{ edit: $wire.entangle('showEdit').live }">
        @if($issue->project->isCompleted())
            <livewire:issues.update-status :issue="$issue"/>
        @else
            @can('update', $issue)
                <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit issue" />
                <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing issue" />
            @endcan
        @endif

        <div x-show="!edit">
            @if($issue->scope?->url)
                <x-forms.field-display variation="inline" label="Page URL">
                    <a href="{{ $issue->scope->url }}" target="_blank">{{ $issue->scope->url }}</a>
                    <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
                </x-forms.field-display>
            @endif
            @if($issue->siaRule)
                <flux:subheading class="items-center">
                    <a href="{{ $this->siteimproveUrl() }}#/sia-r{{ $issue->siaRule->id }}/failed" target="_blank">
                        Siteimprove Issue Detail
                        <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
                    </a>
                </flux:subheading>
            @endif
            <x-forms.field-display variation="inline" label="Target Element">
                {!! $issue->target !!}
            </x-forms.field-display>

            @if($issue->description)
                <x-forms.field-display label="Description">
                    {!! $issue->description !!}
                </x-forms.field-display>
            @endif
        </div>

        <div x-show="edit" x-cloak>
            <livewire:issues.update-issue :$issue />
        </div>
    </div>

    <livewire:items.view-items :$issue />
</div>

{{-- Sidebar for AI help --}}
<x-slot:sidebarPrimary>
    <livewire:issues.issue-sidebar :$issue />
</x-slot:sidebarPrimary>
