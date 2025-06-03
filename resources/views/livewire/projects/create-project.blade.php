<div>
    <h1>Create Project</h1>

    <form wire:submit="save">
        @include('livewire.projects.fields')
        <x-forms.button.submit-group submitName="Create Project"/>
    </form>
</div>

<x-slot:sidebarPrimary>
    <h3>Instructions</h3>
    <p>
        The project name and URL are required, but additional details can be added later.
    </p>

    <div class="expander">
        <h4>Siteimprove report integration</h4>
        <div>
            <p>
                To automatically link Siteimprove issues and page reports, the Site URL must be identical
                to the URL used in Siteimprove. This should be completed before adding Scope items.
            </p>
            <p>
                For the Siteimprove Report URL, the Accessibility report link looks similar to this:
            </p>
                <blockquote class="text-xs! break-words">
                    https://my2.siteimprove.com/Accessibility/848090/NextGen/Overview
                </blockquote>
            <p>
                Note that you do not need to include the query information after
                <span class="text-xs!">"/Overview"</span>.
            </p>
        </div>

        <h4>Report fields</h4>
        <div>
            <p>
                Report fields can be updated at any point.
            </p>
        </div>
    </div>

</x-slot:sidebarPrimary>
