<div class="max-w-[900px]">
    <h1>Report: {{ $project->name }}</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">

        <div class="col-span-1 order-first md:order-last print:hidden">
            <x-forms.button icon="printer" x-on:click="window.print()">Print</x-forms.button>
            <x-forms.button icon="arrow-down-tray" wire:click="exportReport()">Export</x-forms.button>
        </div>

        <div class="col-span-3">
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
                    <td>{{ $project->name }} ({{ $project->site_url }})</td>
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
            </table>
        </div>
    </div>


    <p class="italic">
        Please note: This summary document is not comprehensive of all accessibility issues.
        It represents the major issues that should be prioritized but there are likely additional
        items that will need to be addressed as well. Please continue to utilize
        <a href="https://it.cornell.edu/siteimprove">Cornell’s Siteimprove tool</a>
        as well as other
        <a href="https://it.cornell.edu/accessibility/recommended-web-accessibility-testing-plan">manual testing techniques</a>
        to identify and address WCAG 2 AA compliance issues.
    </p>

    <h3>What is the purpose of the site?</h3>
    {!! $project->site_purpose !!}

    <h3>URLs included in review</h3>
    {!! $project->urls_included !!}

    <h3>URLs excluded from review</h3>
    {!! $project->urls_excluded !!}

    <h3>Link to review and any supporting documents</h3>
    <p>
        <a href="{{ route('project.show', $project->id) }}">{{ $project->name }} Review</a><br>
        [{{ route('project.show', $project->id) }}]
    </p>

    <h3>Testing notes and procedure</h3>
    {!! $project->review_procedure !!}


    <h2>Overview of findings</h2>
    {!! $project->summary !!}

    <h2>List of Issues Found</h2>

    @foreach($this->issues() as $issues)
        @php($scope = $issues[0]->scope)
        <div class="mb-4">
            @if($scope)
                <h3 class="mb-0">{{ $scope->title }}</h3>
                <flux:subheading class="mb-2"><a href="{{ $scope->url }}">{{ $scope->url }}</a></flux:subheading>
            @else
                <h3 class="mb-0">Issues</h3>
            @endif
            @foreach($issues as $issue)
                <h4 class="font-semibold mb-0.5">
                    <x-forms.button
                        href="{{ route('guidelines.show', $issue->guideline) }}"
                        title="View Guideline {{ $issue->guideline->number }}"
                        data-cds-button-assessment
                        class="{{ Str::of($issue->assessment->value())->lower()->replace('/', '') }}"
                        size="xs"
                    >{{ $issue->guideline->number }}</x-forms.button>

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
                    <flux:subheading>Images:</flux:subheading>
                    <div class="flex flex-wrap gap-1 mt-1 mb-4">
                        @foreach($issue->image_links as $imagePath)
                            @php($imageName = pathinfo($imagePath, PATHINFO_BASENAME))
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
