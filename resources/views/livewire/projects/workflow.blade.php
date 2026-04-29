<div>
    <flux:heading size="lg" class="mb-1">Workflow</flux:heading>
    <flux:separator class="mb-2"/>

    <div class="mb-2">
        @if($project->reviewer)
            @can('update-reviewer', [$project, auth()->user()])
                <div class="float-right">
                    @can('manage-projects', $project->team)
                        <flux:modal.trigger name="update-reviewer">
                            <x-forms.button icon="pencil-square" size="xs" title="Edit reviewer" />
                        </flux:modal.trigger>
                    @endcan
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
            @can('update-reviewer', [$project, auth()->user()])
                @can('manage-projects', $project->team)
                    <flux:modal.trigger name="update-reviewer">
                        <x-forms.button icon="plus-circle">Assign Reviewer</x-forms.button>
                    </flux:modal.trigger>
                @else
                    <x-forms.button icon="plus-circle" wire:click.prevent="assignCurrentUser">
                        Assign to Me
                    </x-forms.button>
                @endcan
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

    @if($project->verifier)
        <div class="mb-2">
            @can('update-verifier', [$project, auth()->user()])
                <div class="float-right">
                    @can('manage-projects', $project->team)
                        <flux:modal.trigger name="update-verifier">
                            <x-forms.button icon="pencil-square" size="xs" title="Edit verifier" />
                        </flux:modal.trigger>
                    @endcan
                    <x-forms.button.delete
                        title="Remove {{ $project->verifier->name }} as verifier"
                        size="xs"
                        wire:click.prevent="removeVerifier()"
                        wire:confirm="Are you sure you want to remove &quot;{{ $project->verifier->name }}&quot; as verifier?"
                    />
                </div>
            @endcan
            <x-forms.field-display label="Verifier">
                {{ $project->verifier->name }}
            </x-forms.field-display>
        </div>
    @endif

    @if($project->status->isPostReview() && !$project->status->isClosed())
        <div class="mb-2">
            @empty($project->verifier)
                @can('update-verifier', [$project, auth()->user()])
                    @can('manage-projects', $project->team)
                        <flux:modal.trigger name="update-verifier">
                            <x-forms.button icon="plus-circle">Assign Verifier</x-forms.button>
                        </flux:modal.trigger>
                    @else
                        <x-forms.button icon="plus-circle" wire:click.prevent="assignCurrentUserAsVerifier">
                            Assign to Me
                        </x-forms.button>
                    @endcan
                @else
                    <x-forms.field-display label="Verifier" class="mb-0!">
                        <span class="text-gray-500">No verifier assigned</span>
                    </x-forms.field-display>
                @endcan
            @endif
        </div>
        <flux:modal name="update-verifier" wire:close="closeUpdateVerifier()" class="md:w-96">
            <livewire:projects.update-verifier :project="$project"/>
        </flux:modal>
    @endif

    <div>
        @can('update-status', $project)
            <div class="float-right">
                <flux:modal.trigger name="update-status">
                    <x-forms.button icon="cog-6-tooth" size="xs" title="Edit status" />
                </flux:modal.trigger>
            </div>
        @endcan
        <x-forms.field-display label="Status" class="mb-0!">
            {{ $project->status?->label() ?? \App\Enums\ProjectStatus::NotStarted->label() }}
        </x-forms.field-display>
    </div>

    <flux:modal name="update-status" wire:close="closeUpdateStatus()" class="md:w-96">
        <form class="mb-0!">
            <h3>Update Status</h3>

            {{ $this->statusDescription }}

            <div class="mt-8">
                @if($this->nextActionLabel)
                    <x-forms.button wire:click="updateStatus('next')">{{ $this->nextActionLabel }}</x-forms.button>
                @endif
                @if($this->previousActionLabel)
                    <x-forms.button wire:click="updateStatus('previous')" class="secondary">{{ $this->previousActionLabel }}</x-forms.button>
                @endif
            </div>
        </form>
    </flux:modal>
</div>
