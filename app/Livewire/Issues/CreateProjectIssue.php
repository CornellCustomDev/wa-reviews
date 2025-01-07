<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\ProjectIssueForm;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateProjectIssue extends Component
{
    public ProjectIssueForm $form;
    public Project $project;

    public function save()
    {
        $this->authorize('update', $this->project);
        $issue = $this->form->store($this->project);

        return redirect()->route('issue.show', $issue);
    }
}
