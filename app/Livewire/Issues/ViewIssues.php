<?php

namespace App\Livewire\Issues;

use App\Events\IssueChanged;
use App\Livewire\Forms\IssueForm;
use App\Models\Issue;
use App\Models\Scope;
use Livewire\Attributes\On;
use Livewire\Component;

class ViewIssues extends Component
{
    public Scope $scope;

    public IssueForm $form;
    public int $editingId = 0;

    #[On('issues-updated')]
    public function refreshScope(): void
    {
        $this->scope->refresh();
    }

    public function delete(Issue $issue): void
    {
        $this->authorize('delete', $issue);
        $issue->delete();

        event(new IssueChanged($issue, 'deleted', []));

        $this->dispatch('issues-updated');
    }
}
