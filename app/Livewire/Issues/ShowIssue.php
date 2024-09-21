<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowIssue extends Component
{
    public Issue $issue;

    #[On('items-updated')]
    public function refreshIssue(): void
    {
        $this->issue->refresh();
    }

    public function render()
    {
        $this->authorize('view', $this->issue);
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
