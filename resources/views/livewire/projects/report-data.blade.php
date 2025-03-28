<div>
    <div class="mb-4 max-w-(--breakpoint-md)" x-data="{ editReport: $wire.entangle('showEditReport').live }">
        <div class="col-span-2 border rounded-sm border-cds-gray-200 p-4">
            @can('update', $project)
                <x-forms.button icon="pencil-square" class="float-right" x-show="!editReport" x-on:click="editReport = !editReport" title="Edit report" />
                <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="editReport" x-on:click="editReport = !editReport" title="Cancel editing project" />
            @endcan

            <flux:heading level="2" size="xl">Report Data</flux:heading>

            <div x-show="!editReport">
                <div>
                    @if($project->responsible_unit)
                        <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">Responsible unit at Cornell</flux:heading>
                        <p>{{ $project->responsible_unit }}</p>
                    @endif

                    @if($project->contact_name)
                        <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">Point of Contact</flux:heading>
                        <p>{{ $project->contact_name }}
                            @if($project->contact_netid)
                                ({{ $project->contact_netid }})
                            @endif
                        </p>
                    @endif

                    <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">Who is the audience?</flux:heading>
                    @if($project->audience)
                        <p>{{ $project->audience }}</p>
                    @endif

                    <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">What is the purpose of the site?</flux:heading>
                    <div>{!! $project->site_purpose ?? '' !!}</div>

                    <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">URLs included in review</flux:heading>
                    <div>{!! $project->urls_included ?? '' !!}</div>

                    {{-- <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">URLs excluded from review</flux:heading>
                    <div>{!! $project->urls_excluded ?? '' !!}</div>

                    <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">Review procedure</flux:heading>
                    <div>{!! $project->review_procedure ?? '' !!}</div>

                    <flux:heading level="3" size="lg" class="font-sans font-semibold text-cds-gray-900 text-[15px]">Summary and Overall Findings</flux:heading>
                    <flux:text>{!! $project->summary ?? '' !!}</flux:text> --}}

                    <x-forms.field-display label="URLs excluded from review">
                        {!! $project->urls_excluded ?? '' !!}
                    </x-forms.field-display>

                    <x-forms.field-display label="Review Procedure">
                        {!! $project->review_procedure ?? '' !!}
                    </x-forms.field-display>

                    <x-forms.field-display class="mb-0!" label="Summary and Overall Findings">
                        {!! $project->summary ?? '' !!}
                    </x-forms.field-display>
                </div>
            </div>

            <div x-show="editReport" x-cloak>
                <livewire:projects.update-report :$project />
            </div>
        </div>
    </div>

    <x-forms.button :href="route('project.report', $project)">View Report</x-forms.button>
</div>
