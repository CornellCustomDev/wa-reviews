<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use Livewire\Attributes\On;
use Livewire\Component;

class ShowIssue extends Component
{
    public Issue $issue;

    #[On('issues-updated')]
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
                'breadcrumbs' => [
                    'Projects' => route('projects.index'),
                    $this->issue->project->name => route('projects.show', $this->issue->project),
                    'Viewing Issue' => 'active'
                ],
            ]);
    }
}
