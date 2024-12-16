<div>
    <div class="cwd-component align-right">
        <x-forms.button.back :href="route('scope.show', $issue->scope)" title="Back to Scope" />
    </div>

    <h1>{{ $issue->project->name }}: Issue</h1>

    <div class="col-span-2 border rounded border-cds-gray-200 p-4 mb-8" x-data="{ edit: $wire.entangle('showEdit').live }">
        @can('update', $issue)
            <x-forms.button icon="pencil-square" class="float-right" x-on:click="edit = !edit" title="Edit Issue" />
        @endcan

        <div x-show="!edit">
            <flux:subheading class="items-center">
                <a href="{{ $issue->scope->url }}" target="_blank">{{ $issue->scope->url }}</a>
                <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
            </flux:subheading>
            <flux:heading class="mb-4">
                <flux:icon.cursor-arrow-ripple class="inline-block" variant="mini" />
                {!! $issue->target !!}
            </flux:heading>

            @if($issue->description)
                <p>{!! $issue->description !!}</p>
            @endif
        </div>

        <div x-show="edit">
            <livewire:issues.update-issue :$issue />
        </div>
    </div>

    <livewire:items.view-items :$issue />
</div>

{{-- Sidebar for AI help --}}
<x-slot:sidebarPrimary>
    <livewire:ai.guideline-help :$issue />
    <livewire:guidelines.sidebar />
</x-slot:sidebarPrimary>
