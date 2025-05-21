<?php

namespace App\Livewire\Issues;

use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
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
        $this->authorize('create', [Issue::class, $this->scope->project]);
        $issue = $this->form->store($this->scope);

        return redirect()->route('issue.show', $issue);
    }

    public function render()
    {
        return view('livewire.issues.create-issue')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->scope->project->name => route('project.show', $this->scope->project),
            $this->scope->title => route('scope.show', $this->scope),
            'Add Issue' => 'active'
        ];
    }
}
