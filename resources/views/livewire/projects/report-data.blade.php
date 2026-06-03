<div class="mb-4 max-w-225" x-data="{ editReport: false }" x-on:close-edit="editReport = false; $el.scrollIntoView({ behavior: 'smooth' })">
    @can('update', $project)
        <x-forms.button icon="pencil-square" class="float-right" x-show="!editReport" x-on:click="editReport = !editReport" title="Edit report" />
        <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="editReport" x-on:click="editReport = !editReport" title="Cancel editing project" />
    @endcan

    <div x-show="!editReport">
        <x-forms.field-display label="What is the purpose of the site?">
            {!! nl2br( $project->site_purpose ?: "\n") !!}
        </x-forms.field-display>

        <x-forms.field-display label="URLs included in review">
            {!! nl2br($project->urls_included ?: "\n") !!}
        </x-forms.field-display>

        <x-forms.field-display label="URLs excluded from review">
            {!! nl2br($project->urls_excluded ?: "\n") !!}
        </x-forms.field-display>

        <x-forms.field-display label="Testing notes and procedure">
            {!! nl2br($project->review_procedure ?: "\n") !!}
        </x-forms.field-display>

        <flux:separator class="mb-4"/>

        <h2>Overview of findings</h2>
        {!! $project->summary !!}

        <flux:separator class="mb-4"/>
    </div>

    <div x-show="editReport" x-cloak>
        <livewire:projects.update-report :$project />
    </div>
</div>
