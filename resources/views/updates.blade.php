<x-cd.layout.app title="Updates" :sidebar="true">
    <h1>Updates / Release Notes</h1>

    <flux:callout class="mb-4" color="amber">
        <flux:callout.heading>Work in Progress</flux:callout.heading>
        <flux:callout.text class="text-cds-gray-950!">
            The Web Accessibility Reviews application is currently in pre-release.
            Some information is available via the
            <a href="https://github.com/CornellCustomDev/wa-reviews">GitHub repository</a>.
        </flux:callout.text>
    </flux:callout>

    <livewire:documents.show-document slug="updates" />
</x-cd.layout.app>
