<div>
    <h1>Report: {{ $project->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

        <div class="col-span-1 order-first md:order-last print:hidden">
            <div class="mb-4">
                <x-forms.button icon="printer" x-on:click="window.print()">Print</x-forms.button>
                <flux:dropdown>
                    <x-forms.button icon="arrow-down-tray" size="xs" class="text-sm! px-3 h-8">Export...</x-forms.button>
                    <x-forms.menu>
                        <x-forms.menu.item icon="arrow-top-right-on-square" href="{{ route('project.report.google', $project) }}" target="_blank">
                            Google Sheet (requires login)
                        </x-forms.menu.item>
                        <x-forms.menu.item icon="clipboard-document" href="{{ route('project.report.raw', $project) }}" target="_blank">
                            Raw (for copy/paste)
                        </x-forms.menu.item>
                    </x-forms.menu>
                </flux:dropdown>
            </div>

            @if($project->isInProgress())
                @can('update', $report)
                    <div class="mb-4 pt-4 border-t border-cds-gray-200">
                        <x-forms.button wire:click="completeReview" :disabled="! $report->isReady()" >Complete Review</x-forms.button>
                    </div>
                @endcan
            @endif

            {{-- Report Viewers (visible from InProgress onward) --}}
            @unless($project->status->isNotStarted())
                @can('update-report-viewers', $project)
                    <livewire:projects.report-viewers :project="$project"/>
                @endcan
            @endunless
        </div>

        <div class="col-span-2">
            <table class="table bordered">
                <tr>
                    <th style="width: 200px">Prepared by</th>
                    <td>{{ $project->reviewer->name }} ({{ $project->reviewer->email }})</td>
                </tr>
                <tr>
                    <th>Date review completed</th>
                    <td>{{ $project->completed_at?->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <th>Site</th>
                    <td>
                        <div class="wrap-break-word max-w-125">
                            {{ $project->name }} ({{ $project->site_url }})
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Responsible unit at Cornell</th>
                    <td>{{ $project->responsible_unit }}</td>
                </tr>
                <tr>
                    <th>Point of contact</th>
                    <td>{{ $project->contact_name }} ({{ $project->contact_netid }})</td>
                </tr>
                <tr>
                    <th>Audience</th>
                    <td>{{ $project->audience }}</td>
                </tr>
                <tr>
                    <th>Link to review</th>
                    <td>
                        <a href="{{ route('project.show', $project->id) }}">{{ $project->name }} Review</a>
                        [{{ route('project.show', $project->id) }}]
                    </td>
                </tr>
            </table>
        </div>
    </div>

<div class="max-w-225">
    <p class="italic">
        Please note: This summary document is not comprehensive of all accessibility issues.
        It represents the major issues that should be prioritized but there are likely additional
        items that will need to be addressed as well. Please continue to utilize
        <a href="https://it.cornell.edu/siteimprove">Cornell's Siteimprove tool</a>
        as well as other
        <a href="https://it.cornell.edu/accessibility/recommended-web-accessibility-testing-plan">manual testing techniques</a>
        to identify and address WCAG 2 AA compliance issues.
    </p>

    <livewire:projects.report-data :$report />

    <h2>List of Issues Found</h2>

    @foreach($this->issues() as $issues)
        @php($scope = $issues[0]->scope)
        <div class="mb-4">
            <h3>{{ $scope?->title ?? 'Issues' }}</h3>
            @foreach($issues as $issue)
                <h4 class="font-semibold mb-0.5">
                    <x-forms.button
                        href="{{ route('guidelines.show', $issue->guideline) }}"
                        title="View Guideline {{ $issue->guideline->number }}"
                        data-cds-button-assessment
                        class="{{ Str::of($issue->assessment->value())->lower()->replace('/', '') }}"
                        size="xs"
                    >{{ $issue->getGuidelineInstanceNumber() }}</x-forms.button>

                    <span>{{ $issue->guideline->name }}</span>
                </h4>
                @if($issue->siaRule)
                    <a href="{{ $this->siteimproveUrl($scope) }}#/sia-r{{ $issue->siaRule->id }}/failed" target="_blank">
                        Siteimprove Issue Detail
                        <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
                    </a>
                @endif
                <h5>WCAG 2 Success Criterion: {{ $issue->guideline->criterion->getLongName() }}</h5>
                <x-forms.field-display label="Assessment" variation="inline" @class(['mb-0!' => $issue->impact])>
                    {{ $issue->assessment->getDescription() }}
                </x-forms.field-display>

                @if($issue->impact)
                    <x-forms.field-display label="Impact" variation="inline">
                        {{ $issue->impact->value() }}
                    </x-forms.field-display>
                @endif

                @if($issue->scope)
                    <x-forms.field-display label="Page URL">
                        <a href="{{ $issue->scope->url }}">{{ $issue->scope->url }}</a>
                    </x-forms.field-display>
                @endif

                <x-forms.field-display label="Location">
                    {{ $issue->target }}
                </x-forms.field-display>
                <x-forms.field-display label="Observation">
                    {!! $issue->description !!}
                </x-forms.field-display>
                <x-forms.field-display label="Recommendation for remediation">
                    {!! $issue->recommendation !!}
                </x-forms.field-display>
                @if($issue->testing)
                    <x-forms.field-display label="Testing">
                        {!! $issue->testing !!}
                    </x-forms.field-display>
                @elseif($issue->testing_method)
                    <x-forms.field-display label="Test Method" variation="inline">
                        {{ $issue->testing_method }}
                    </x-forms.field-display>
                @endif

                @if($issue->image_links)
                    <flux:subheading>Images/Files:</flux:subheading>
                    <div class="flex flex-wrap gap-1 mt-1 mb-4">
                        @foreach($issue->image_links as $imagePath)
                            @php($fileType = pathinfo($imagePath, PATHINFO_EXTENSION))
                            @php($imageName = pathinfo($imagePath, PATHINFO_BASENAME))
                            @if(in_array($fileType, ['pdf', 'eml']))
                                <div class="max-w-60 bg-black/60 text-white text-center p-2 print:hidden">
                                    <a href="{{ $imagePath }}" class="text-white" target="_blank" rel="noopener noreferrer">
                                        <i class="fa fa-file"></i> {{ $imageName }}
                                    </a>
                                </div>
                                <div class="not-print:hidden">
                                    {{ $imagePath }}
                                </div>
                            @else
                                @if(in_array($fileType, ['mp4', 'webm']))
                                    <div class="relative print:hidden">
                                        <video src="{{ $imagePath }}" class="max-w-60" controls></video>
                                        <div class="absolute bottom-0 left-0 w-full bg-black/60 text-white text-center p-1">
                                            {{ $imageName }}
                                        </div>
                                    </div>
                                    <div class="not-print:hidden">
                                        {{ $imagePath }}
                                    </div>
                                @else
                                    <flux:tooltip position="bottom" class="align-middle">
                                        <flux:button wire:click="viewImage('{{ $imagePath }}')" :loading="false" class="px-0.5! overflow-hidden hover:border-cds-blue-900 h-auto">
                                            <div class="relative py-1">
                                                <img
                                                    src="{{ $imagePath }}"
                                                    alt="Preview of image: {{ $imageName }}"
                                                    class="max-w-60"
                                                />
                                            </div>
                                        </flux:button>
                                        <flux:tooltip.content>
                                            View image {{ $imageName }}
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                    <div class="not-print:hidden">
                                        {{ $imagePath }}
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>
        @if(!$loop->last)
            <hr>
        @endif
    @endforeach
    <flux:modal name="view-image" class="max-w-4xl" wire:close="closeImage()">
        @if ($selectedImage)
            <flux:subheading class="mb-2">{{ basename($selectedImage) }}</flux:subheading>
            <div class="border border-cds-gray-900">
                <img src="{{ $selectedImage }}" alt="Selected Image" class="w-full h-auto">
            </div>
        @endif
    </flux:modal>
</div>
</div>
