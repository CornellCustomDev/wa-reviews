<div>
    <h1>Report: {{ $project->name }}</h1>

    <div class="float-right print:hidden">
        <x-forms.button icon="printer" x-on:click="window.print()">Print</x-forms.button>
        <x-forms.button icon="arrow-down-tray" wire:click="exportReport()">Export</x-forms.button>
    </div>

    @include('livewire.projects.report-intro')

    <h2>Overview of findings</h2>
    <p>...</p>

    <h2>List of Issues Found</h2>

    @foreach($this->issues() as $scope => $issues)
        @php($scope = $issues[0]->scope)
        <div class="mb-4">
            <h3 class="mb-0">Source: {{ $scope->title }}</h3>
            <flux:subheading class="mb-2">(<a href="{{ $scope->url }}">{{ $scope->url }}</a>)</flux:subheading>
            @foreach($issues as $issue)
                @foreach($issue->items as $item)
                    <h4 class="text-blue-900 font-semibold mb-0.5">
                        <x-forms.button
                            size="xs"
                            class="text-black hover:text-white bg-wa-{{ Str::of($item->assessment->value())->lower()->replace('/', '') }} text-sm! h-5 pt-0.5 mr-1"
                            href="{{ route('guidelines.show', $item->guideline) }}" title="View Guideline {{ $item->guideline->number }}"
                        >{{ $item->guideline->number }}</x-forms.button>

                        <span>{{ $item->guideline->name }}</span>
                    </h4>
                    @if($issue->siaRule)
                        <a href="{{ $this->siteimproveUrl($scope) }}#/sia-r{{ $issue->siaRule->id }}/failed" target="_blank">
                            Siteimprove Issue Detail
                            <flux:icon.clipboard-document-list class="inline-block text-cds-gray-700 -mt-1" />
                        </a>
                    @endif
                    <h5>WCAG 2 Success Criterion: {{ $item->guideline->criterion->getLongName() }}</h5>

                    <flux:subheading>Location</flux:subheading>
                    <p>{{ $item->issue->target }}</p>

                    <flux:subheading>Observation</flux:subheading>
                    {!! $item->description !!}

                    <flux:subheading>Recommendation</flux:subheading>
                    {!! $item->recommendation !!}


                    <flux:subheading>Testing</flux:subheading>
                    <div class="mb-4">
                        {!! $item->testing !!}
                    </div>

                    @if($item->image_links)
                        <flux:subheading>Images:</flux:subheading>
                        <div class="flex flex-wrap gap-1 mt-1 mb-4">
                            @foreach($item->image_links as $imagePath)
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
