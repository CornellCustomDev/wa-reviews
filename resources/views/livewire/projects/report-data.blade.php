<div class="mb-4 max-w-225" x-data="{ editReport: false }" x-on:close-edit="editReport = false; $el.scrollIntoView({ behavior: 'smooth' })">
    @can('update', $report)
        <x-forms.button icon="pencil-square" class="float-right" x-show="!editReport" x-on:click="editReport = !editReport" title="Edit report" />
        <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="editReport" x-on:click="editReport = !editReport" title="Cancel editing report" />
    @endcan

    <div x-show="!editReport">
        <x-forms.field-display label="What is the purpose of the site?">
            {!! nl2br($report->project->site_purpose ?: "\n") !!}
        </x-forms.field-display>

        <x-forms.field-display label="URLs included in review">
            {!! nl2br($report->urls_included ?: "\n") !!}
        </x-forms.field-display>

        <x-forms.field-display label="URLs excluded from review">
            {!! nl2br($report->urls_excluded ?: "\n") !!}
        </x-forms.field-display>

        <x-forms.field-display label="Testing notes and procedure">
            {!! nl2br($report->review_procedure ?: "\n") !!}
        </x-forms.field-display>

        <flux:separator class="mb-4"/>

        <h2>Overview of findings</h2>
        {!! $report->summary !!}

        <flux:separator class="mb-4"/>
    </div>

    <div x-show="editReport" x-cloak>
        <livewire:projects.update-report :$report />
    </div>
</div>
