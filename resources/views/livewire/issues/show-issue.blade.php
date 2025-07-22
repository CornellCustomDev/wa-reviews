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
        <div class="float-right">
            @if($issue->project->isCompleted())
                <livewire:issues.update-status :issue="$issue"/>
            @else
                @can('update', $issue)
                    <x-forms.button icon="pencil-square" class="float-right" x-show="!edit" x-on:click="edit = !edit" title="Edit issue" />
                    <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="edit" x-on:click="edit = !edit" title="Cancel editing issue" />
                @endcan
            @endif
        </div>

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

            @if(!empty($issue->image_links))
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach(Arr::wrap($issue->image_links) as $imagePath)
                        @if(!Str::contains($imagePath, config('app.url')))
                            <flux:subheading class="items-center">
                                <a href="{{ $imagePath }}" target="_blank">
                                    Image {{ $loop->iteration }}
                                </a> <flux:icon.arrow-top-right-on-square class="inline-block -mt-1" variant="micro" />
                            </flux:subheading>
                            @continue
                        @endif
                        @php($imageName = pathinfo($imagePath, PATHINFO_BASENAME))
                        <flux:tooltip position="bottom" class="align-middle">
                            <flux:button wire:click="viewImage('{{ $imagePath }}')" :loading="false" class="px-0.5! w-24 h-24 overflow-hidden hover:border-cds-blue-900">
                                <div class="relative w-full h-full">
                                    <img
                                        src="{{ $imagePath }}"
                                        alt="Preview of image: {{ $imageName }}"
                                        class="absolute top-0.5 left-0 object-cover object-top rounded-sm"
                                    />
                                </div>
                            </flux:button>
                            <flux:tooltip.content>
                                View image {{ $imageName }}
                            </flux:tooltip.content>
                        </flux:tooltip>
                    @endforeach
                </div>
            @endif

            @if($issue->assessment && $issue->guideline)
                <div data-cds-field-display>
                    <flux:heading level="3">
                        Assessment
                        <flux:tooltip toggleable position="right" class="align-middle">
                            @if($issue->hasUnreviewedAi())
                                <flux:button icon="sparkles" size="xs" variant="ghost" class="text-red-600!"/>
                                <x-forms.button
                                    title="Accept AI recommendation"
                                    icon="hand-thumb-up" size="xs"
                                    wire:click="acceptAI"
                                />
                                <x-forms.button
                                    title="Reject AI recommendation"
                                    icon="hand-thumb-down" size="xs"
                                    wire:click="rejectAI"
                                />
                            @elseif($issue->isAiAccepted())
                                <flux:button icon="sparkles" size="xs" variant="ghost" class="text-cds-blue-600!"/>
                            @endif

                            <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                @if($issue->hasUnreviewedAi())
                                    <flux:heading class="text-white">This AI-generated response needs to be reviewed</flux:heading>
                                @endif
                                <flux:subheading class="text-white">AI Reasoning</flux:subheading>
                                <blockquote class="mb-0 pl-2">{!! $issue->ai_reasoning  !!}</blockquote>
                            </flux:tooltip.content>
                        </flux:tooltip>
                    </flux:heading>

                    <p>
                    <x-forms.button
                        data-cds-button-assessment
                        class="{{ Str::of($issue->assessment->value())->lower()->replace('/', '') }}"
                        size="xs"
                        x-on:click.prevent="$dispatch('show-guideline', {number: {{ $issue->guideline->number }} })"
                    >
                        {{ $issue->guideline->getNumber() }}
                    </x-forms.button>

                    @if($issue->guideline->number < 100)
                        {{ $issue->assessment->getDescription() }} for Guideline {{ $issue->guideline->number }} - {{ $issue->guideline->name }}
                        <br>
                            {{ $issue->guideline->getCriterionInfo() }}
                    @else
                        {{ $issue->assessment->getDescription() }} for Best Practice
                        <br>
                            {{ $issue->guideline->name }}
                    @endif
                    </p>
                </div>

                @if($issue->impact)
                    <x-forms.field-display label="Impact" variation="inline">
                        <flux:badge data-cds-impact class="{{ Str::of($issue->impact->value())->lower() }}" size="sm">
                            {{ $issue->impact->value() }}
                        </flux:badge>
                        {{ $issue->impact->getLongDescription() }}
                    </x-forms.field-display>
                @endif
            @endif

            @if($issue->recommendation)
                <x-forms.field-display label="Recommendation">
                    {!! $issue->recommendation !!}
                </x-forms.field-display>
            @endif

            @if($issue->content_issue)
                <p>
                <flux:icon.information-circle class="inline-block text-cds-blue-600" variant="mini"/> Content entry issue
                </p>
            @endif

            @if($issue->testing && !($issue->testing->isEmpty()))
                <x-forms.field-display label="Testing">
                    {!! $issue->testing !!}
                </x-forms.field-display>
            @elseif($issue->testing_method)
                <x-forms.field-display label="Testing Method">
                    {{ $issue->testing_method->value() }}
                </x-forms.field-display>
            @endif
        </div>

        <div x-show="edit" x-cloak>
            <livewire:issues.update-issue :$issue />
        </div>
    </div>

    <flux:modal name="view-image" class="max-w-4xl" wire:close="closeImage()">
        @if ($selectedImage)
            <flux:subheading class="mb-2">{{ basename($selectedImage) }}</flux:subheading>
            <div class="border border-cds-gray-900">
                <img src="{{ $selectedImage }}" alt="Selected Image" class="w-full h-auto">
            </div>
        @endif
    </flux:modal>
</div>

{{-- Sidebar for AI help --}}
<x-slot:sidebarPrimary>
    <livewire:issues.issue-sidebar :$issue />
</x-slot:sidebarPrimary>
