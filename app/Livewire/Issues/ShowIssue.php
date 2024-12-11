<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class ShowIssue extends Component
{
    public Issue $issue;

    #[Url(as: 'e', history: true)]
    public bool $showEdit = false;

    #[On('items-updated')]
    public function refreshIssue(): void
    {
        $this->issue->refresh();
    }

    public function render()
    {
        $this->authorize('view', $this->issue);
        if ($this->showEdit === true && !Gate::allows('update', $this->issue)) {
            $this->showEdit = false;
        }

        return view('livewire.issues.show-issue')
            ->layout('components.layouts.app', [
                'sidebar' => true,
                'breadcrumbs' => $this->getBreadcrumbs(),
            ]);
    }

    protected function getBreadcrumbs(): array
    {
        return [
            'Projects' => route('projects'),
            $this->issue->project->name => route('project.show', $this->issue->project),
            $this->issue->scope->title => route('scope.show', $this->issue->scope),
            'Viewing Issue' => 'active'
        ];
    }
}
