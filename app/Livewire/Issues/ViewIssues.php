<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Project;
use App\Models\Issue;
use Livewire\Component;

class ViewIssues extends Component
{
    public IssueForm $form;
    public Project $project;
    public int $editingId = 0;

    public function render()
    {
        $this->authorize('view', $this->project);

        return view('livewire.issues.view-issues', [
            'issues' => $this->project->issues,
        ]);
    }

    public function delete(Issue $issue): void
    {
        $this->authorize('delete', $issue);

        $issue->delete();
    }
}
