<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Scope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class UpdateIssue extends Component
{
    public IssueForm $form;
    public Scope $scope;

    public function mount(Scope $scope, Issue $issue)
    {
        $this->scope = $scope;
        $this->form->setModel($issue);
    }

    public function save()
    {
        $this->authorize('update', $this->form->issue);
        $this->form->update();

        return redirect()->route('issue.show', $this->form->issue);
    }
}
