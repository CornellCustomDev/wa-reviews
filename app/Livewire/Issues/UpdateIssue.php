<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Project;
use App\Models\Issue;
use Livewire\Component;

class UpdateIssue extends Component
{
    public IssueForm $form;
    public Project $project;

    public function mount(Project $project, Issue $issue)
    {
        $this->project = $project;
        $this->form->setModel($issue);
    }

    public function save()
    {
        $this->authorize('update', $this->form->issue);
        $this->form->update();

        return redirect()->route('projects.show', [$this->project]);
    }

    public function render()
    {
        return view('livewire.issues.update-issue');
    }
}
