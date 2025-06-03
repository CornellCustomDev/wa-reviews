<x-cd.layout.app title="Help" :sidebar="false">
    <h1>Help</h1>
    <p>
        This application is designed to help you document web accessibility reviews
        according to <a href="https://docs.google.com/document/d/16b77RZcTL0bWZXTkMgJNd0rfkrv-x83jktacVB8NY-U/edit?tab=t.0#heading=h.oab37tgoxnuc">
            Cornell's WCAG testing guidelines.
        </a>
    </p>

    <flux:callout class="mb-4">
        <flux:callout.heading>Work in Progress</flux:callout.heading>
        <flux:callout.text class="text-cds-gray-950!">
            This help documentation has not been fully written yet. If you have questions or need assistance, please contact the Web Accessibility Team.
        </flux:callout.text>
    </flux:callout>

    <h2>Getting Started</h2>
    <p>
        To begin, you will need to create a new project. This can be done from the Projects page or from a specific Team's Projects section.
    </p>

    <h2>Steps to Perform a Review</h2>
    <ol>
        <li><strong>Create a New Review Project:</strong> Navigate to Projects or Teams → Team → Projects and create a new project.</li>
        <li><strong>Add Scope:</strong> On the Project page, add scope items in the Scope tab. You can also import scopes from a spreadsheet checklist.</li>
        <li><strong>Assign the Project to a Reviewer:</strong> In the Workflow panel, set the reviewer and update the status to Start Review.</li>
        <li><strong>Add Issues:</strong> Add issues on the Project page via the Issues tab or directly from a Scope.</li>
        <li><strong>Add Observations:</strong> You can manually add observations of success criteria failure or use AI to generate them (note that AI observations must be approved).</li>
        <li><strong>Finish and Report:</strong> Fill out the fields on the Report tab, update the status to Complete Review in the Workflow panel, and view the report.</li>
    </ol>

</x-cd.layout.app>
