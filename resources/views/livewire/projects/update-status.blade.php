<div>
    <flux:heading size="lg" class="mb-1">Workflow</flux:heading>
    <flux:separator class="mb-2"/>

    <div class="mb-2">
        @if($project->reviewer)
            @can('manage-project', $project)
                <div class="float-right">
                    <flux:modal.trigger name="update-reviewer">
                        <x-forms.button icon="pencil-square" size="xs" title="Edit reviewer" />
                    </flux:modal.trigger>
                    <x-forms.button.delete
                        title="Remove {{ $project->reviewer->name }} from project"
                        size="xs"
                        wire:click.prevent="removeReviewer"
                        wire:confirm="Are you sure you want to remove &quot;{{ $project->reviewer->name }}&quot; from the project?"
                    />
                </div>
            @endcan
            <x-forms.field-display label="Reviewer">
                {{ $project->reviewer->name }}
            </x-forms.field-display>
        @else
            @can('manage-project', $project)
                <flux:modal.trigger name="update-reviewer">
                    <x-forms.button icon="plus-circle">Assign Reviewer</x-forms.button>
                </flux:modal.trigger>
            @else
                <x-forms.field-display label="Reviewer" class="mb-0!">
                    <span class="text-gray-500">No reviewer assigned</span>
                </x-forms.field-display>
            @endcan
        @endif
    </div>
    <flux:modal name="update-reviewer" wire:close="closeUpdateReviewer()" class="md:w-96">
        <livewire:projects.update-reviewer :project="$project"/>
    </flux:modal>

    <div>
        @can('update', $project)
            <div class="float-right">
                <flux:modal.trigger name="update-status">
                    <x-forms.button icon="cog-6-tooth" size="xs" title="Edit reviewer" />
                </flux:modal.trigger>
            </div>
        @endcan
        <x-forms.field-display label="Status" class="mb-0!">
            {{ $project->status ?? 'Not started' }}
        </x-forms.field-display>
    </div>

    <flux:modal name="update-status" wire:close="closeUpdateStatus()" class="md:w-96">
        <form class="mb-0!">
            <h3>Update Status</h3>

            @switch($project->status)
                @case(\App\Enums\ProjectStatus::Completed)
                    The work was marked complete, but you can re-open it if you need to make changes.
                    <div class="mt-8">
                        <x-forms.button wire:click="updateStatus('previous')" class="secondary">Re-open Review</x-forms.button>
                    </div>
                    @break
                @case(\App\Enums\ProjectStatus::InProgress)
                    If the work is finished, mark it as completed in order to make it available in a read-only view.
                    <div class="mt-8">
                        <x-forms.button wire:click="updateStatus('next')">Complete Review</x-forms.button>
                        <x-forms.button wire:click="updateStatus('previous')" class="secondary">Stop Review</x-forms.button>
                    </div>
                    @break
                @default()
                    @if($project->reviewer)
                        Are you ready to have {{ $project->reviewer->name }} start work on the project?
                    @else
                        No reviewer has been assigned to this project. Are you sure you want to start the review?
                    @endif
                    <div class="mt-8">
                        <x-forms.button wire:click="updateStatus('next')">Start Review</x-forms.button>
                    </div>
                    @break
            @endswitch

        </form>
    </flux:modal>
</div>
