<x-cd.layout.app title="Help" :sidebar="true" x-data="{
        openSection(heading) {
            heading.classList.toggle('open');
            const button = heading.querySelector('.expander-button');
            if (button) {
                const isOpen = heading.classList.contains('open');
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
        }
    }">
    <div>
        <h1>Help Documentation</h1>

        <p>
            The Web Accessibility Reviews application helps you conduct and document accessibility reviews
            according to
            <a href="https://docs.google.com/document/d/16b77RZcTL0bWZXTkMgJNd0rfkrv-x83jktacVB8NY-U/edit?tab=t.0#heading=h.oab37tgoxnuc">Cornell's WCAG 2.2 AA testing guidelines</a>.
        </p>

        <div class="expander">
            <h2 id="understanding-the-application" x-ref="overview">Understanding the Application</h2>
            <div>
                <p>
                    The application is organized around <strong>Projects</strong>, which represent individual accessibility reviews. Each project contains:
                </p>
                <ul>
                    <li><strong>Scope items</strong> - The web pages or components being reviewed</li>
                    <li><strong>Issues</strong> - Accessibility problems identified during the review</li>
                    <li><strong>Observations</strong> - Specific instances of accessibility criteria failures</li>
                    <li><strong>Report data</strong> - Summary information and recommendations</li>
                </ul>
                <p>
                    The application follows Cornell's WCAG testing guidelines, which are based on the Web Content Accessibility Guidelines (WCAG) 2.2 AA standards.
                </p>
            </div>

            <h2 id="review-workflow" x-ref="workflow">Review Workflow</h2>
            <div>
                <p>
                    The accessibility review process follows these steps:
                </p>
                <ol>
                    <li>
                        <strong>Create a Project</strong>
                        <p>Start by creating a new review project from the Projects page or from a Team's Projects page. Projects can be set up like creating a new WA Checklist, or data can be imported via the Upload button.</p>
                    </li>
                    <li>
                        <strong>Define the Scope</strong>
                        <p>Add scope items via the Scope tab on the Project page. You can manually add URLs or import scopes from a spreadsheet checklist using the standard sheets format.</p>
                    </li>
                    <li>
                        <strong>Assign a Reviewer</strong>
                        <p>In the Workflow panel, assign the project to a reviewer and update the status to Start Review.</p>
                    </li>
                    <li>
                        <strong>Document Issues</strong>
                        <p>Add accessibility issues via the Issues tab on the Project page or from a Scope page. For each issue, you can add observations manually or use AI to suggest observations (AI suggestions must be approved to be included in reports).</p>
                    </li>
                    <li>
                        <strong>Complete the Review</strong>
                        <p>Fill out report fields, update the status to Complete Review in the Workflow panel, and view the report. You can add read-only viewers via the Report Viewers panel.</p>
                    </li>
                </ol>
            </div>

            <h2 id="ai-assistance" x-ref="ai">Using AI Assistance</h2>
            <div>
                <p>
                    The application includes AI-powered features to help with accessibility reviews:
                </p>
                <ul>
                    <li>
                        <strong>AI Chat</strong>
                        <p>Ask questions about accessibility guidelines and best practices. The AI has access to Cornell's accessibility guidelines and can provide context-specific advice.</p>
                    </li>
                    <li>
                        <strong>Page Analysis</strong>
                        <p>Get AI-powered analysis of web pages to identify potential accessibility issues.</p>
                    </li>
                    <li>
                        <strong>Automated Suggestions</strong>
                        <p>Receive AI-generated suggestions for observations based on issue descriptions. These suggestions must be reviewed and approved before being included in reports.</p>
                    </li>
                </ul>
            </div>

            <h2 id="user-roles" x-ref="users">User Roles and Permissions</h2>
            <div>
                <p>
                    The application uses a team-based approach to manage access:
                </p>
                <ul>
                    <li>
                        <strong>Site Administrators</strong>
                        <p>Can create teams, manage system-wide settings, and access all projects.</p>
                    </li>
                    <li>
                        <strong>Team Administrators</strong>
                        <p>Can manage team users and projects within their teams.</p>
                    </li>
                    <li>
                        <strong>Reviewers</strong>
                        <p>Can work on projects assigned to them, add issues, and complete reviews.</p>
                    </li>
                    <li>
                        <strong>Report Viewers</strong>
                        <p>Have read-only access to specific reports they've been granted access to.</p>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <x-slot:sidebarPrimary>
        <h2 id="toc">Table of Contents</h2>
        <ul>
            <li><a href="#understanding-the-application" x-on:click="openSection($refs.overview)">Understanding the Application</a></li>
            <li><a href="#review-workflow" x-on:click.prevent="openSection($refs.workflow)">Review Workflow</a></li>
            <li><a href="#ai-assistance" x-on:click="openSection($refs.ai)">Using AI Assistance</a></li>
            <li><a href="#user-roles" x-on:click="openSection($refs.users)">User Roles and Permissions</a></li>
            <flux:separator class="my-4"/>
            <li><a href="{{ route('updates') }}">Updates and Release Notes</a></li>
        </ul>
    </x-slot:sidebarPrimary>
</x-cd.layout.app>
