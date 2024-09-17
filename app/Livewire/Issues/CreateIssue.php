<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Project;
use App\Models\Issue;
use Livewire\Component;

class CreateIssue extends Component
{
    public IssueForm $form;
    public Project $project;

    public function save()
    {
        $this->authorize('create', [Issue::class, $this->project]);
        $issue = $this->form->store($this->project);

        return redirect()->route('issues.show', [$this->project, $issue]);
    }

    public function render()
    {
        return view('livewire.issues.create-issue')
            ->layout('components.layouts.app');
    }
}
