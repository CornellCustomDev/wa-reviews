<?php

namespace App\Livewire\Projects;

use App\Events\IssueChanged;
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
        return $this->project->issues()
            ->with([
                'scope:id,title',
                'guideline:id,number,name,criterion_id,category_id',
                'guideline.criterion:id,number,name,level',
                'guideline.category:id,name',
            ])
            ->get()
            ->sortBy(['guideline_id', 'scope.id']);
    }

    public function deleteIssue(Issue $issue): void
    {
        $this->authorize('delete', $issue);
        $issue->delete();

        event(new IssueChanged($issue, 'deleted', []));
    }
}
