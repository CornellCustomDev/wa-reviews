<?php

namespace App\Livewire\Projects;

use App\Models\Issue;
use App\Models\Project;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Issues extends Component
{
    public Project $project;

    #[Computed]
    public function issues()
    {
        return $this->project->issues()->with('scope')->get()
            ->sortBy(['scope.id', 'issue.id']);
    }

    public function deleteIssue(Issue $issue): void
    {
        $this->authorize('delete', $issue);
        $issue->delete();
    }
}
