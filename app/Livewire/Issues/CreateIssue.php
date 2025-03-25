<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Scope;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CreateIssue extends Component
{
    public IssueForm $form;
    public Scope $scope;

    public function save()
    {
        $this->authorize('create', [Scope::class, $this->scope->project]);
        $issue = $this->form->store($this->scope);

        return redirect()->route('issue.show', $issue);
    }
}
