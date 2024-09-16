<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('projects.show', $issue->project) }}" title="Back to Project" />
    </div>

    <h1>{{ $issue->project->name }}: Issue</h1>

    <table class="table bordered">
        <tr>
            <th>Target</th>
            <td><livewire:issues.issue-field :$issue field="target" label="Target" /></td>
        </tr>
        <tr>
            <th>Description</th>
            <td><livewire:issues.issue-field :$issue field="description" label="Description" /></td>
        </tr>
    </table>

    <livewire:items.view-items :$issue />
</div>

{{-- Sidebar for AI help --}}
<x-slot:sidebarPrimary>
    <livewire:ai.guideline-help :$issue />
</x-slot:sidebarPrimary>
