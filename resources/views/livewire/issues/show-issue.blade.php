<div>
    <div class="cwd-component align-right">
        <x-forms.link-button route="{{ route('scope.show', $issue->scope) }}" title="Back to Scope" />
    </div>

    <h1>{{ $issue->project->name }}: Issue</h1>

    <table class="table bordered">
        <tr>
            <th>Project</th>
            <td>{{ $issue->project->name }}</td>
        </tr>
        <tr>
            <th>Scope</th>
            <td><a href="{{ $issue->scope->url }}">{{ $issue->scope->title }}</a></td>
        </tr>
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
